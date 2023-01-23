<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Resolve;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use Doctrine\Common\Util\ClassUtils;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;

use function spl_object_hash;

/**
 * A field resolver that uses the Doctrine Laminas hydrator.
 */
class FieldResolver
{
    /**
     * Cache all hydrator extract operations based on spl object hash
     *
     * @var mixed[]
     */
    private array $extractValues = [];

    public function __construct(protected Config $config, protected Metadata $metadata)
    {
    }

    /** @throws Error */
    public function __invoke(mixed $source, mixed $args, mixed $context, ResolveInfo $info): mixed
    {
        assert(is_object($source), 'A non-object was passed to the FieldResolver.  '
            . 'Verify you\'re wrapping your Doctrine GraohQL type() call in a connection.');

        $entityClass   = ClassUtils::getRealClass($source::class);
        $splObjectHash = spl_object_hash($source);

        /**
         * For disabled hydrator cache, store only last hydrator result and reuse for consecutive calls
         * then drop the cache if it doesn't hit.
         */
        if (! $this->config->getUseHydratorCache()) {
            if (isset($this->extractValues[$splObjectHash])) {
                return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
            } else {
                $this->extractValues = [];
            }

            $this->extractValues[$splObjectHash] = $this->metadata
                ->get($entityClass)->getHydrator()->extract($source);

            return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
        }

        // Use full hydrator cache
        if (isset($this->extractValues[$splObjectHash][$info->fieldName])) {
            return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
        }

        $this->extractValues[$splObjectHash] = $this->metadata
            ->get($entityClass)->getHydrator()->extract($source);

        return $this->extractValues[$splObjectHash][$info->fieldName] ?? null;
    }
}
