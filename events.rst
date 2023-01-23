Events
======

A PSR-14 event dispatcher is included for listening to events.

Filtering Query Builders
------------------------

Each top level connection uses a QueryBuilder object.  This QueryBuilder
object may be modified to filter the data for the logged in user.  This can be
used as a security layer and can be used to make customizations to QueryBuilder
objects.  QueryBuilders are built then triggered through an event.
Listen to this event and modify the passed QueryBuilder to apply your security.

Event names are passed as a second parameter to a ``$driver->resolve()``.  The
default event name is 'filter.querybuilder'.

You may specify an event name to resolve a connection.  Only this event will
fire when the QueryBuilder is created.  The default 'filter.querybuilder' will
not fire.

In the code below the custom event ``Artist::class . '.filterQueryBuilder'`` will fire:

.. code-block:: php

  <?php

  use ApiSkeletons\Doctrine\GraphQL\Driver;
  use App\ORM\Entity\Artist;
  use GraphQL\Type\Definition\ObjectType;
  use GraphQL\Type\Schema;

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

To listen for this event and add filtering, such as filtering for the current
user, create at least one listener.  You may add multiple listeners.

.. code-block:: php

  <?php

  use ApiSkeletons\Doctrine\GraphQL\Event\FilterQueryBuilder;
  use League\Event\EventDispatcher;

  $driver->get(EventDispatcher::class)->subscribeTo(Artist::class . '.filterQueryBuilder',
      function(FilterQueryBuilder $event) {
          $event->getQueryBuilder()
              ->innerJoin('entity.user', 'user') // The default entity alias is always `entity`
              ->andWhere($event->getQueryBuilder()->expr()->eq('user.id', ':userId'))
              ->setParameter('userId', currentUser()->getId())
              ;
      }
  );

The ``FilterQueryBuilder`` event has two functions:

* ``getQueryBuilder`` - Will return a query builder with the user specified
  filters already applied.
* ``getEntityAliasMap`` - Returns an array of entities used in the QueryBuilder
  and the aliases used for each.  Use this to help you apply more filters to
  the QueryBuider.


Modify an Entity Definition
---------------------------

You may modify the array used to define an entity type before it is created.
This can be used for generated data and the like.  You must attach to events
before defining your GraphQL schema.

Events of this type are named ``Entity::class . '.definition'`` and the event
name cannot be modified.

.. code-block:: php

  <?php

  use ApiSkeletons\Doctrine\GraphQL\Driver;
  use ApiSkeletons\Doctrine\GraphQL\Event\EntityDefinition;
  use App\ORM\Entity\Artist;
  use GraphQL\Type\Definition\ResolveInfo;
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

The ``EntityDefinition`` event has one function:

* ``getDefinition`` - Will return an ArrayObject with the ObjectType definition.
  Because this is an ArrayObject you may manipulate it as
  needed and the value is set by reference, just like the
  QueryBuilder event above.

.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
