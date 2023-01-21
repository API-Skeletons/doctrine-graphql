About
=====

Authored by Tom H Anderson <tom.h.anderson@gmail.com> of
`API Skeletons <https://apiskeletons.com>`_
and a member of the `Doctrine Maintainers <https://www.doctrine-project.org/team/maintainers.html>`_.

This project provides a Driver to be used with
`GraphQL <https://github.com/webonyx/graphql-php>`_ for PHP.

You may choose which entities, fields, and associations in your object manager
are available for querying through GraphQL.  Filtering is provided for
top-level GraphQL types (those types assigned to fields on the query object
type) and for all association collections below that type.

Pagination of collections is supported with 
`GraphQL's Complete Connection Model <https://graphql.org/learn/pagination/#complete-connection-model>`_.

.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
