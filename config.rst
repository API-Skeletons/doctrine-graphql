Driver Config
=============

The ``Driver`` takes a second, optional, argument of type
``ApiSkeletons\Doctrine\GraphQL\Config``.  The constructor of ``Config`` takes
an array parameter.

The parameter options are:

``group``
--------- 
Each attribute has an optional ``group`` parameter that allows
for multiple configurations within the entities.  Specify the group in the
``Config`` to load only those attributes with the same ``group``.

``globalEnable``
----------------
When set to true all fields and all associations will be
enabled.  This is best used as a development setting when
the entities are subject to change.

``globalIgnore``
----------------
When ``globalEnable`` is set to true, this array of field and associations names
will be excluded from the schema.  For instance ``['password']`` is a good choice
to ignore globally.

``globalByValue``
-----------------
This overrides the ``byValue`` entity attribute globally.  When set to true 
all hydrators will extract by value.  When set to false all hydrators will
extract by reference.  When not set the individual entity attribute value
is used and that is, by default, extract by value.

``limit``
--------- 
A hard limit for all queries throughout the entities.  Use this
to prevent abuse of GraphQL.  Default is 1000.

``useHydratorCache``
-------------------- 
When set to true hydrator results will be cached for
the duration of the request thereby saving multiple extracts for
the same entity.  Default is ``false``.

Creating a ``Driver`` with all config options:

.. code-block:: php
  :linenos:

  <?php

  use ApiSkeletons\Doctrine\GraphQL\Config;
  use ApiSkeletons\Doctrine\GraphQL\Driver;

  $driver = new Driver($entityManager, new Config[
      'group' => 'customGroup',
      'globalEnable' => true,
      'limit' => 500,
      'useHydratorCache' => true,
  ]);


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
