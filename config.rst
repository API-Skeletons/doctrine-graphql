Driver Config
=============

The ``Driver`` takes a second, optional, argument of type
``ApiSkeletons\Doctrine\GraphQL\Config``.  The constructor of ``Config`` takes
an array parameter.

The parameter options are:

* ``group`` - Each attribute has an optional ``group`` parameter that allows
  for multiple configurations within the entities.  Specify the group in the
  ``Config`` to load only those attributes with the same ``group``.
* ``limit`` - A hard limit for all queries throughout the entities.  Use this
  to prevent abuse of GraphQL.  Default is 1000.
* ``useHydratorCache`` - When set to true hydrator results will be cached for
  the duration of the request thereby saving multiple extracts for
  the same entity.  Default is ``false``.
* ``usePartials`` - Instead of hydrating complete entities when data is fetched
  using Doctrine, `partials <https://www.doctrine-project.org/projects/doctrine-orm/en/2.11/reference/partial-objects.html>`_
  can be used.  Be sure to understand the use of partials in Doctrine if you
  choose to use this option.  Default is ``false``.

Creating a ``Driver`` with all config options:

.. code-block:: php
  :linenos:

  use ApiSkeletons\Doctrine\GraphQL\Config;
  use ApiSkeletons\Doctrine\GraphQL\Driver;

  $driver = new Driver($entityManager, new Config[
      'group' => 'customGroup',
      'limit' => 500,
      'useHydratorCache' => true,
      'usePartials' => true,
  ]);


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
