<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once __DIR__ . '/vendor/autoload.php';

// Create a simple "default" Doctrine ORM configuration for Annotations
$config = Setup::createAttributeMetadataConfiguration([__DIR__ . '/test/Entity'], true);

// database configuration parameters
$conn = array(
    'driver' => 'pdo_sqlite',
    'dbname' => ':memory:',
);

// obtaining the entity manager
$entityManager = EntityManager::create($conn, $config);

return ConsoleRunner::createHelperSet($entityManager);

