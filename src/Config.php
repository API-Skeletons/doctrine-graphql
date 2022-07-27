<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL;

/**
 * This class is used for parameter differentiation when creating the driver
 *   $partialContext->setLimit(1000);
 */
class Config
{
    /**
     * @var string The GraphQL group. This allows multiple GraphQL
     *               configurations within the same application or
     *               even within the same group of entities and Object Manager.
     */
    protected string $group;

    /**
     * @var bool When set to true hydrator results will be cached for the
     *           duration of the request thereby saving multiple extracts for
     *           the same entity.
     */
    protected bool $useHydratorCache;

    /** @var int A hard limit for fetching any collection within the schema */
    protected int $limit;

    /**
     * @var bool When set to true all fields and all associations will be
     *           enabled.  This is best used as a development setting when
     *           the entities are subject to change.
     */
    protected bool $globalEnable;

    /**
     * @param mixed[] $config
     */
    public function __construct(array $config = [])
    {
        $this->group            = $config['group'] ?? 'default';
        $this->useHydratorCache = $config['useHydratorCache'] ?? false;
        $this->limit            = $config['limit'] ?? 1000;
        $this->globalEnable    = $config['globalEnable'] ?? false;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getUseHydratorCache(): bool
    {
        return $this->useHydratorCache;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getGlobalEnable(): bool
    {
        return $this->globalEnable;
    }
}
