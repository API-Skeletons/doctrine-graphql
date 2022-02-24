<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Field;

use ApiSkeletons\Doctrine\GraphQL\Driver;
use Doctrine\Common\Util\ClassUtils;
use GraphQL\Type\Definition\ResolveInfo;
use ApiSkeletons\Doctrine\GraphQL\Context;

/**
 * A field resolver that uses the Doctrine Laminas hydrator.
 */
class FieldResolver
{
    /**
     * Cache all hydrator extract operations based on spl object hash
     *
     * @var array
     */
    private $extractValues = [];

    protected Driver $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function __invoke($source, $args, Context $context, ResolveInfo $info)
    {
        if (is_array($source)) {
            return $source[$info->fieldName];
        }

        $entityClass = ClassUtils::getRealClass(get_class($source));
        $splObjectHash = spl_object_hash($source);

        $hydrator = $this->driver->getMetadata()->getEntity($entityClass)->getHydrator();

        /**
         * For disabled hydrator cache, store only last hydrator result and reuse for consecutive calls
         * then drop the cache if it doesn't hit.
         */
        if (! $this->driver->getConfig()->getUseHydratorCache()) {
            if (isset($this->extractValues[$splObjectHash])) {
                return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
            } else {
                $this->extractValues = [];
            }

            $this->extractValues[$splObjectHash] = $hydrator->extract($source);

            return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
        }

        // Use full hydrator cache
        if (isset($this->extractValues[$splObjectHash][$info->fieldName])) {
            return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
        }

        $this->extractValues[$splObjectHash] = $hydrator->extract($source);

        return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
    }
}
