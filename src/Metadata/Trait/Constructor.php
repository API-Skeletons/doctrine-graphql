<?php

namespace ApiSkeletons\Doctrine\GraphQL\Metadata\Trait;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

trait Constructor
{
    protected ContainerInterface $container;
    protected EntityManager $entityManager;
    protected array $metadata;

    public function __construct(ContainerInterface $container, EntityManager $entityManager, array $metadata)
    {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->metadata = $metadata;
    }
}
