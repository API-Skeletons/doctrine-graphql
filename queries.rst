Running Queries
===============

This section is intended for the developer who needs to write queries against
an implementation of this repository.

Queries are not special to this repository.  The format of queries are exactly
what GraphQL is spec'd out to be.  For each implementation of GraphQL the
filtering of data is not defined.  In order to build the filters for this
an underscore approach is used.  `fieldName_filter` is the format for all
filters.

Pagination of collections supports
`GraphQL's Complete Connection Model <https://graphql.org/learn/pagination/#complete-connection-model>`_.

An example query:

Fetch at most 100 performances in CA for each artist with 'Dead' in their name.

.. code-block:: php

  <?php

  $query = "{
      artist ( filter: { name_contains: \"Dead\" } ) {
        edges {
          node {
            name
            performance ( filter: { _limit: 100 state:\"CA\" } ) {
              edges {
                node {
                  performanceDate venue
                }
              }
            }
          }
        }
      }
  }";


Filters
-------

For each field, which is not a reference to another entity, a colletion of
filters exist. Given an entity which contains a `name` field you may directly
filter the name using

.. code-block:: js

    filter: { name: "Grateful Dead" }

You may only use each field's filter once per filter().  Should a child record
have the same name as a parent it will share the filter names but filters are
specific to the entity they filter upon.

Provided Filters::

    fieldName_eq         -  Equals; same as name: value.
                              DateTime not supported.
    fieldName_neq        -  Not Equals
    fieldName_gt         -  Greater Than
    fieldName_lt         -  Less Than
    fieldName_gte        -  Greater Than or Equal To
    fieldName_lte        -  Less Than or Equal To
    fieldName_in         -  Filter for values in an array
    fieldName_notin      -  Filter for values not in an array
    fieldName_between    -  Filter between `from` and `to` values.  Good substitute for DateTime Equals.
    fieldName_contains   -  Strings only. Similar to a Like query as `like '%value%'`
    fieldName_startswith -  Strings only. A like query from the beginning of the value `like 'value%'`
    fieldName_endswith   -  Strings only. A like query from the end of the value `like '%value'`
    fieldName_isnull     -  If TRUE return results where the field is null.
    fieldName_sort       -  Sort the result by this field.  Value is 'asc' or 'desc'


The format for using these filters is:

.. code-block:: js

    filter: { name_endswith: "Dead" }

For isnull the parameter is a boolean

.. code-block:: js

    filter: { name_isnull: false  }

For in and notin an array of values is expected

.. code-block:: js

    filter: { name_in: ["Phish", "Legion of Mary"] }

For the between filter two parameters are necessary.  This is very useful for
date ranges and number queries.

.. code-block:: js

    filter: { year_between: { from: 1966 to: 1995 } }


To select a distinct list of years

.. code-block:: js

    {
      artist ( filter: { id:2 } ) {
        edges {
          node {
            performance( filter: { year_sort: "asc" } ) {
              edges {
                node {
                  year
                }
              }
            }
          }
        }
      }
    }


All filters are **AND** filters.  For **OR** support use multiple
queries and aggregate them.


Pagination
----------

Pagination of collections supports
`GraphQL's Complete Connection Model <https://graphql.org/learn/pagination/#complete-connection-model>`_.

A complete query for all pagination data

.. code-block:: js

  {
    artist(filter: {_first: 10, _after: "cursor"}) {
      totalCount
      pageInfo {
        endCursor
        hasNextPage
      }
      edges {
        cursor
        node {
          id
        }
      }
    }
  }

Cursors are included with each edge.  A cursor is a base64 encoded
offset from the beginning of the result set.

Two pairs of parameters work with the query:

* ``_first`` and ``_after``
* ``_last`` and ``_before``

* ``_first`` corresponds to the items per page starting from the beginning;
* ``_after`` corresponds to the cursor from which the items are returned.
* ``_last`` corresponds to the items per page starting from the end;
* ``_before`` corresponds to the cursor from which the items are returned, from a backwards point of view.

To get the first page specify the number of edges

.. code-block:: js

  {
    artist(filter: {_first: 10}) {
    }
  }

To get the next page, you would add the endCursor from the current page as the after parameter.

.. code-block:: js

  {
    artist(filter: {_first: 10, _after: "endCursor"}) {
    }
  }

For the previous page, you would add the startCursor from the current page as the before parameter.

.. code-block:: js

  {
    offers(filter: {_last: 10, _before: "startCursor"}) {
    }
  }

.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst
