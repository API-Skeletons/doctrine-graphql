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
    protected string $group = 'default';

    /**
     * @var string|null The group is usually suffixed to GraphQL type names.
     *                  You may specify a different string for the group suffix
     *                  or you my supply an empty string to exclude the suffix.
     *                  Be warned, using the same groupSuffix with two different
     *                  groups can cause collisions.
     */
    protected ?string $groupSuffix = null;

    /**
     * @var bool When set to true hydrator results will be cached for the
     *           duration of the request thereby saving multiple extracts for
     *           the same entity.
     */
    protected bool $useHydratorCache = false;

    /** @var int A hard limit for fetching any collection within the schema */
    protected int $limit = 1000;

    /**
     * @var bool When set to true all fields and all associations will be
     *           enabled.  This is best used as a development setting when
     *           the entities are subject to change.
     */
    protected bool $globalEnable = false;

    /** @var string[] An array if field names to ignore when using globalEnable. */
    protected array $globalIgnore = [];

    /**
     * @var bool|null When set to true, all entities will be extracted by value
     *           across all hydrators in the driver.  When set to false,
     *           all hydrators will extract by reference.  This overrides
     *           per-entity attribute configuration.
     */
    protected ?bool $globalByValue = null;

    /**
     * @var string|null When set, the entityPrefix will be removed from each
     *                  type name.  This simplifies type names and makes reading
     *                  the GraphQL documentation easier.
     */
    protected ?string $entityPrefix = null;

    /**
     * @param mixed[] $config
     */
    public function __construct(array $config = [])
    {
        /**
         * Dynamic setters will fail for invalid settings and allow for
         * validation of field types through setters
         */
        foreach ($config as $setting => $value) {
            $setter = 'set' . $setting;
            $this->$setter($value);
        }
    }

    protected function setGroup(string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    protected function setGroupSuffix(?string $groupSuffix): self
    {
        $this->groupSuffix = $groupSuffix;

        return $this;
    }

    public function getGroupSuffix(): ?string
    {
        return $this->groupSuffix;
    }

    protected function setUseHydratorCache(bool $useHydratorCache): self
    {
        $this->useHydratorCache = $useHydratorCache;

        return $this;
    }

    public function getUseHydratorCache(): bool
    {
        return $this->useHydratorCache;
    }

    protected function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    protected function setGlobalEnable(bool $globalEnable): self
    {
        $this->globalEnable = $globalEnable;

        return $this;
    }

    public function getGlobalEnable(): bool
    {
        return $this->globalEnable;
    }

    /**
     * @param string[] $globalIgnore
     */
    protected function setGlobalIgnore(array $globalIgnore): self
    {
        $this->globalIgnore = $globalIgnore;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getGlobalIgnore(): array
    {
        return $this->globalIgnore;
    }

    protected function setGlobalByValue(?bool $globalByValue): self
    {
        $this->globalByValue = $globalByValue;

        return $this;
    }

    public function getGlobalByValue(): ?bool
    {
        return $this->globalByValue;
    }

    protected function setEntityPrefix(?string $entityPrefix): self
    {
        $this->entityPrefix = $entityPrefix;

        return $this;
    }

    public function getEntityPrefix(): ?string
    {
        return $this->entityPrefix;
    }
}
