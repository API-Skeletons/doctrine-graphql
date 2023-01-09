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

Using custom event names for resolving specific connections
-----------------------------------------------------------

You may specify an event name to resolve a connection.  Only this event will
fire when the QueryBuilder is created.  The default 'filter.querybuilder' will
not fire.

In the code below the custom event ``artist.specific.event`` will fire:

.. code-block:: php

  <?php

  $schema = new Schema([
    'query' => new ObjectType([
        'name' => 'query',
        'fields' => [
            'artist' => [
                'type' => $driver->connection($driver->type(Artist::class)),
                'args' => [
                    'filter' => $driver->filter(Artist::class),
                ],
                'resolve' => $driver->resolve(Artist::class, 'artist.specific.event'),
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

  $driver->get(EventDispatcher::class)->subscribeTo('artist.specific.event',
      function(FilterQueryBuilder $event) {
          $event->getQueryBuilder()
              ->innerJoin('artist.user', 'user')
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

.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
