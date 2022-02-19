GraphQL for Doctrine Using Attributes
=====================================

[![Build Status](https://travis-ci.org/API-Skeletons/doctrine-graphql.svg)](https://travis-ci.org/API-Skeletons/doctrine-graphql)
[![Coverage](https://coveralls.io/repos/github/API-Skeletons/doctrine-graphql/badge.svg?branch=master&124)](https://coveralls.io/repos/github/API-Skeletons/doctrine-graphql/badge.svg?branch=master&124)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)
[![Gitter](https://badges.gitter.im/api-skeletons/open-source.svg)](https://gitter.im/api-skeletons/open-source)
[![Patreon](https://img.shields.io/badge/patreon-donate-yellow.svg)](https://www.patreon.com/apiskeletons)
[![Total Downloads](https://poser.pugx.org/api-skeletons/doctrine-graphql/downloads)](https://packagist.org/packages/api-skeletons/doctrine-graphql)

This library is framework agnostic.  Using PHP 8 attributes on your entities, this library will create a driver
for use with [webonyx/graphql-php](https://github.com/webonyx/graphql-php).

Quick Start
-----------

Adding attributes to your entities will take time and is covered in the documentation.
Once your entities are properly attributed the following code will create an `artist`
entry point in GraphQL with full deep traversal of related entities and collections.

```php
$graphQLDriver = new DoctrineGraphQLDriver($entityManager, $config);

$schema = new Schema([
    'query' => new ObjectType([
        'name' => 'query',
        'fields' => [
            'artist' => [
                'type' => Type::listOf($graphQLDriver->type(Entity\Artist::class),
                'args' => [
                    'filter' => $graphQLDriver->filter(Entity\Artist::class),
                ],
                'resolve' => $graphQLDriver->resolve(Entity\Artist::class),
            ],
        ],
    ]),
]);

```


Attributes
----------


```php
namespace App\ORM\Entity;

use ApiSkeletons\Doctrine\GraphQL\Attribute as GraphQL;
use App\ORM\Hydrator\CustomHydrator;

#[GraphQL\Entity(group: 'default', hydrator: CustomHydrator::class)]
class User
{
    #[GraphQL\Field]
    private string $id;  // bigint = string

    #[GraphQL\Field]
    private string $name;

    private string $password; // no attribute = not returned in graphql
}
```



[Read the Documentation](https://apiskeletons-doctrine-graphql.readthedocs.io/en/latest/)
==========================================================

