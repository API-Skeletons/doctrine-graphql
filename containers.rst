Containers
==========

Internal to the classes used in this library, PSR-11 containers are used.
You can set values in the containers using ``container->set($id, $value);``.
If a value already exists for the ``$id`` then it will be overwritten.

Containers will execute any ``Closure`` found when getting from itself and pass
the container to the closure as the only argument.  This provides a basic
method for using factories.  Once a factory has executed the result will
replace the factory so later requests will just get the composed object.

There are two containers you should be aware of if you intened to extend this
library.

* ``TypeManager`` - The ``TypeManager`` stores all the GraphQL types created or
  used in the library.  If you want to specify your own type for a field you'll
  need to add your custom type to the container.

  .. code-block:: php
    :linenos:

    <?php

    use ApiSkeletons\Doctrine\GraphQL\Driver;
    use ApiSkeletons\Doctrine\GraphQL\Type\TypeManager;
    use GraphQL\Type\Definition\Type;

    $driver = new Driver($this->getEntityManager());
    $typeManager = $driver->get(TypeManager::class);

    $typeManager->set('customtype', fn() => Type::string());


Custom Types
------------

For instance, if your schema has a ``date`` type, that datatype is not suppored
by default in this library.  But adding the type is just a matter of creating a 
new Date type (modifying the DateTime class is uncomplicated) then adding the 
type to the type manager.

  .. code-block:: php
    :linenos:

    $driver->get(TypeManager::class)
        ->set('date', fn() => new Type\Date());


Custom Hydrators
----------------

* ``HydratorFactory`` - The ``HydratorFactory`` stores hydrator strategies,
  filter classes, naming strategy classes, and all the generated hydrators.

  .. code-block:: php
    :linenos:

    <?php

    use ApiSkeletons\Doctrine\GraphQL\Driver;
    use ApiSkeletons\Doctrine\GraphQL\Hydrator\HydratorFactory;

    $driver = new Driver($this->getEntityManager());
    $hydratorFactory = $driver->get(HydratorFactory::class);

    $hydratorFactory->set('customstrategy', fn() => new CustomStrategy());

.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
