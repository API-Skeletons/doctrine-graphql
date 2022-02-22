<?php

namespace ApiSkeletons\Doctrine\GraphQL;

/**
 * This class is used for parameter differentiation when creating the driver
$partialContext->setLimit(1000);
$partialContext->setUsePartials(true);

 */
class Config
{
    /**
     * @var string[] The GraphQL group. This allows multiple GraphQL
     *               configurations within the same application or
     *               even within the same group of entities and Object Manager.
     */
    private string $group;

    /**
     * @var bool When set to true hydrator results will be cached for the
     *           duration of the request thereby saving multiple extracts for
     *           the same entity.
     */
    private bool $useHydratorCache;

    /**
     * @var int A hard limit for fetching any collection within the schema
     */
    private int $limit;

    /**
     * @var bool Instead of fetching entire entities enabling this will use
     *           partial objects. This is a performance feature and is
     *           defaulted to false. Before enabling this be sure to
     *           understand Doctrine Partial Objects
     */
    private bool $usePartials;

    /**
     * @var string[] A list of entities to allow with default, non-attribute
     *               configuration.  '*' is accepted for all entities.  You
     *               can still configure entities in the list with attributes
     *               but entities not in this list will be excluded.
     */
    private array $allowList = [];

    public function __construct(array $config)
    {
        $this->group = (string)$config['group'] ?? 'default';
        $this->useHydratorCache = (bool)$config['useHydratorCache'] ?? false;
        $this->limit = (int)$config['limit'] ?? 0;
        $this->usePartials = (bool)$config['usePartials'] ?? false;
        $this->allowList = $config['allowList'] ?? [];
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

    public function getUsePartials(): bool
    {
        return $this->usePartials;
    }

    public function getAllowList(): array
    {
        return $this->allowList;
    }
}
