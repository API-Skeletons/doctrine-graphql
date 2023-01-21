<p align="center">
    <img src="https://raw.githubusercontent.com/API-Skeletons/doctrine-graphql/master/banner.png" width="450px">
</p>

GraphQL Type Driver for Doctrine ORM
====================================

[![Build Status](https://github.com/API-Skeletons/doctrine-graphql/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/API-Skeletons/doctrine-graphql/actions/workflows/continuous-integration.yml?query=branch%3Amain)
[![Code Coverage](https://codecov.io/gh/API-Skeletons/doctrine-graphql/branch/main/graphs/badge.svg)](https://codecov.io/gh/API-Skeletons/doctrine-graphql/branch/main)
[![PHP Version](https://img.shields.io/badge/PHP-8.0%2b-blue)](https://img.shields.io/badge/PHP-8.0%2b-blue)
[![Total Downloads](https://poser.pugx.org/api-skeletons/doctrine-graphql/downloads)](//packagist.org/packages/api-skeletons/doctrine-graphql)
[![License](https://poser.pugx.org/api-skeletons/doctrine-graphql/license)](//packagist.org/packages/api-skeletons/doctrine-graphql)


This library provides a framework agnostic GraphQL driver for Doctrine ORM for use with [webonyx/graphql-php](https://github.com/webonyx/graphql-php).  Configuration is available from zero to verbose.  Multiple configurations for multiple drivers are supported.

[Detailed documentation](https://apiskeletons-doctrine-graphql.readthedocs.io/en/latest/) is available.


Library Highlights
------------------

* Uses [PHP 8 Attributes](https://apiskeletons-doctrine-graphql.readthedocs.io/en/latest/attributes.html)
* [Multiple independent configurations](https://apiskeletons-doctrine-graphql.readthedocs.io/en/latest/config.html)
* Support for all default [Doctrine Types](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/types.html) & custom types
* Support for the [GraphQL Complete Connection Model](https://graphql.org/learn/pagination/#complete-connection-model)
* Supports [filtering of sub-collections](https://apiskeletons-doctrine-graphql.readthedocs.io/en/latest/queries.html)
* [PSR-11 containers](https://apiskeletons-doctrine-graphql.readthedocs.io/en/latest/containers.html) used throughout
* [PSR-14 events](https://apiskeletons-doctrine-graphql.readthedocs.io/en/latest/events.html)
* Uses the [Doctrine Laminas Hydrator](https://www.doctrine-project.org/projects/doctrine-laminas-hydrator/en/3.1/index.html) for extraction
* Conforms to the [Doctrine Coding Standard](https://www.doctrine-project.org/projects/doctrine-coding-standard/en/9.0/index.html)
* Written by a [Doctrine Maintainer](https://apiskeletons-doctrine-graphql.readthedocs.io/en/latest/about.html)

Installation
------------

Run the following to install this library using [Composer](https://getcomposer.org/):

```bash
composer require api-skeletons/doctrine-graphql
```

Entity Relationship Diagram
---------------------------

[This Entity Relationship Diagram](https://raw.githubusercontent.com/API-Skeletons/doctrine-graphql/master/test/doctrine-graphql.skipper), created with [Skipper](https://skipper18.com), is used for the query examples below and testing this library.

![Entity Relationship Diagram](https://raw.githubusercontent.com/API-Skeletons/doctrine-graphql/master/test/doctrine-graphql.png)


Enable GraphQL on an entire Entity Manager (quickest start)
----------------------------------------------------------

```php
use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Driver;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;

$driver = new Driver($entityManager, new Config([
    'globalEnable' => true,
]);

$schema = new Schema([
    'query' => new ObjectType([
        'name' => 'query',
        'fields' => [
            'artists' => [
                'type' => $driver->connection($driver->type(Artist::class)),
                'args' => [
                    'filter' => $driver->filter(Artist::class),
                ],
                'resolve' => $driver->resolve(Artist::class),
            ],
        ],
    ]),
]);

$query = '{ 
    artists { 
        edges { 
            node { 
                id 
                name 
                performances { 
                    edges { 
                        node { 
                            venue 
                        } 
                    } 
                } 
            } 
        } 
    }
}';

$result = GraphQL::executeQuery(
    schema: $schema,
    source: $query,
    variableValues: null,
    operationName: null
);
$output = $result->toArray();

```

This quickest start example uses a feature of this library that turns an entire Doctrine schema
into GraphQL without any configuration of the entities by enabling [globalEnable](https://apiskeletons-doctrine-graphql.readthedocs.io/en/latest/config.html#globalenable), intended only for development, in the driver configuration.


Quick Start
-----------

For finer control over your GraphQL, add attributes to your Doctrine entities

```php
use ApiSkeletons\Doctrine\GraphQL\Attribute as GraphQL;

#[GraphQL\Entity]
class Artist 
{
    #[GraphQL\Field]
    public $id;
    
    #[GraphQL\Field]
    public $name;
    
    #[GraphQL\Association]
    public $performances;
}

#[GraphQL\Entity]
class Performance
{
    #[GraphQL\Field]
    public $id;
    
    #[GraphQL\Field]
    public $venue;
    
    /**
     * Not all fields need attributes.
     * Only add attribues to fields you want available in GraphQL
     */
    public $city;
}
```

Create the driver and GraphQL schema

```php
use ApiSkeletons\Doctrine\GraphQL\Driver;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

$driver = new Driver($entityManager);

$schema = new Schema([
    'query' => new ObjectType([
        'name' => 'query',
        'fields' => [
            'artists' => [
                'type' => $driver->connection($driver->type(Artist::class)),
                'args' => [
                    'filter' => $driver->filter(Artist::class),
                ],
                'resolve' => $driver->resolve(Artist::class),
            ],
        ],
    ]),
    'mutation' => new ObjectType([
        'name' => 'mutation',
        'fields' => [
            'artistUpdateName' => [
                'type' => $driver->type(Artist::class),
                'args' => [
                    'id' => Type::nonNull(Type::id()),
                    'input' => Type::nonNull($driver->input(Artist::class, ['name'])),
                ],
                'resolve' => function ($root, $args): Artist {
                    $artist = $this->getEntityManager()->getRepository(Artist::class)
                        ->find($args['id']);

                    $artist->setName($args['input']['name']);
                    $this->getEntityManager()->flush();

                    return $artist;
                },
            ],
        ],
    ]),
]);
```

Run GraphQL queries

```php
use GraphQL\GraphQL;

$query = '{ 
    artists { 
        edges { 
            node { 
                id 
                name 
                performances { 
                    edges { 
                        node { 
                            venue 
                        } 
                    } 
                } 
            } 
        } 
    }
}';

$result = GraphQL::executeQuery(
    schema: $schema,
    source: $query,
    variableValues: null,
    operationName: null
);

$result = GraphQL::executeQuery($schema, $query);
$output = $result->toArray();
```

Run GraphQL mutations

```php
use GraphQL\GraphQL;

$query = 'mutation {
    artistUpdateName(id: 1, input: { name: "newName" }) {
        id
        name
    }
}';

$result = GraphQL::executeQuery($schema, $query);
$output = $result->toArray();
```


Events
------

### Filter Query Builder

You may modify the query builder used to resolve any connection by subscribing to events.
Each connection may have a unique event name.  `Entity::class . '.filterQueryBuilder'` is recommended.
Pass as the second parameter to `$driver->resolve()`.

```php
use ApiSkeletons\Doctrine\GraphQL\Event\FilterQueryBuilder;
use App\ORM\Entity\Artist;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use League\Event\EventDispatcher;

$schema = new Schema([
  'query' => new ObjectType([
      'name' => 'query',
      'fields' => [
          'artists' => [
              'type' => $driver->connection($driver->type(Artist::class)),
              'args' => [
                  'filter' => $driver->filter(Artist::class),
              ],
              'resolve' => $driver->resolve(Artist::class, Artist::class . '.filterQueryBuilder'),
          ],
      ],
  ]),
]);

$driver->get(EventDispatcher::class)->subscribeTo(Artist::class . '.filterQueryBuilder',
    function(FilterQueryBuilder $event) {
        $event->getQueryBuilder()
            ->innerJoin('entity.user', 'user')
            ->andWhere($event->getQueryBuilder()->expr()->eq('user.id', ':userId'))
            ->setParameter('userId', currentUser()->getId())
            ;
    }
);
```

### Entity ObjectType Definition

You may modify the array used to define an entity type before it is created. This can be used for generated data and the like. 
You must attach to events before defining your GraphQL schema.  See the [detailed documentation](https://apiskeletons-doctrine-graphql.readthedocs.io/en/latest/events.html#modify-an-entity-definition) for details.

```php
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Event\EntityDefinition;
use App\ORM\Entity\Artist;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use League\Event\EventDispatcher;

$driver = new Driver($entityManager);

$driver->get(EventDispatcher::class)->subscribeTo(
    Artist::class . '.definition',
    static function (EntityDefinition $event): void {
        $definition = $event->getDefinition();

        // In order to modify the fields you must resovle the closure
        $fields = $definition['fields']();

        // Add a custom field to show the name without a prefix of 'The'
        $fields['nameUnprefix'] = [
            'type' => Type::string(),
            'description' => 'A computed dynamically added field',
            'resolve' => static function ($objectValue, array $args, $context, ResolveInfo $info): mixed {
                return trim(str_replace('The', '', $objectValue->getName()));
            },
        ];

        $definition['fields'] = $fields;
    }
);
```

### Entity Filter

You may modify the array used to define an entity filter before it is created. This can be used for generated data and the like.
You must attach to events before defining your GraphQL schema.  See the [detailed documentation](https://apiskeletons-doctrine-graphql.readthedocs.io/en/latest/events.html#modify-an-entity-filter) for details.

```php
use ApiSkeletons\Doctrine\GraphQL\Driver;
use ApiSkeletons\Doctrine\GraphQL\Event\EntityFilter;
use App\ORM\Entity\Artist;
use GraphQL\Type\Definition\Type;
use League\Event\EventDispatcher;

$driver = new Driver($entityManager);

$driver->get(EventDispatcher::class)->subscribeTo(
    Artist::class . '.filter',
    static function (EntityFilter $event): void {
        $definition = $event->getDefinition();

        // In order to modify the fields you must resovle the closure
        $fields = $definition['fields']();

        // Add a custom field to hold a custom filter value
        $fields['performanceCount_gte'] = [
            'type' => Type::int(),
            'description' => 'The number of performances for this artist greater than or equals',
        ];

        $definition['fields'] = $fields;
    }
);
```

Filtering
---------

For every attributed field and every attributed association, filters are available in your
GraphQL query.

Example

```gql
{
  artists (filter: { name_contains: "dead" }) {
    edges {
      node {
        id
        name
        performances (filter: { venue_eq: "The Fillmore" }) {
          edges { 
            node {
              venue
            }
          }
        }
      }
    }
  }
}
```

Filters are named after their field and followed by an underscore and the filter name.

* eq - Equals.  May be shorthanded as `field: "value"` or longhanded as `field_eq: "value"`
* neq - Not equals
* lt - Less than
* lte - Less than or equal to
* gt - Greater than
* gte - Greater than or equal to
* isnull - Is null.  If value is true, the field must be null.  If value is false, the field must not be null.
* between - Between.  Identical to using gte & lte on the same field.  Give values as `low,high`
* in - Exists within a list of comma-delimited values.
* notin - Does not exist within a list of comma-delimited values.
* startwith - A like query with a wildcard on the right side of the value.
* endswith - A like query with a wildcard on the left side of the value.
* contains - A like query.


Further Reading
---------------

[Detailed documentation](https://apiskeletons-doctrine-graphql.readthedocs.io/en/latest/)
is available.
