Installation
============

Installation of this module uses composer. For composer documentation, please
refer to `getcomposer.org <http://getcomposer.org/>`_ ::

    $ composer require api-skeletons/doctrine-graphql

Once installed, add **ApiSkeletons\\Doctrine\\GraphQL** to your list of modules inside
`config/application.config.php` or `config/modules.config.php`.


laminas-component-installer
---------------------------

If you use `laminas-component-installer <https://github.com/laminas/laminas-component-installer>`_,
that plugin will install **ApiSkeletons\\Doctrine\\GraphQL**  as a module for you.


API-Skeletons/doctrine-criteria configuration
----------------------------------

You must copy the config for API-Skeletons/doctrine-criteria to your autoload directory::

    $ cp vendor/api-skeletons/doctrine-criteria/config/apiskeletons-doctrine-criteria.global.php.dist config/autoload/apiskeletons-doctrine-criteria.global.php

.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
