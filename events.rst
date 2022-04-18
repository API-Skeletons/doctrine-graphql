Events
======

A PSR-14 event dispatcher is included for listening to events.

Filtering Query Builders
------------------------

Each top level type uses a QueryBuilder object.  This QueryBuilder
object should be modified to filter the data for the logged in user.  This is
the security layer.  QueryBuilders are built then triggered through an event.
Listen to this event and modify the passed QueryBuilder to apply your security.

.. code-block:: php
  :linenos:

  <?php

  use ApiSkeletons\Doctrine\GraphQL\Driver;
  use ApiSkeletons\Doctrine\GraphQL\Event\FilterQueryBuilder;
  use Doctrine\ORM\QueryBuilder;
  use League\Event\EventDispatcher;

  $driver = new Driver($this->getEntityManager());

  $driver->get(EventDispatcher::class)->subscribeTo('filter.querybuilder',
      function(FilterQueryBuilder $event) {
          assert(QueryBuilder::class, $event->getQueryBuilder());
          assert([
              'entity' => 'App\ORM\Entity\Artist'
          ] === $event->getEntityAliasMap());
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
