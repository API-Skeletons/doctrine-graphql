Running Mutations
=================

Mutations modify data in your Doctrine ORM.  They are defined as such:

.. code-block:: php
  :linenos:

  <?php

  $schema = new Schema([
      'mutation' => new ObjectType([
          'name' => 'mutation',
          'fields' => [
              'mutationName' => [
                  'type' => $driver->type(Artist::class),
                  'args' => [
                      'id' => Type::nonNull(Type::id()),
                      'input' => Type::nonNull($driver->input(Artist::class, ['name'])),
                  ],
                  'resolve' => function ($root, $args): User {
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

You can define multiple mutations under the ``fields`` array.  The ``type`` is
the GraphQL type of the entity you're processing.  The ``args`` array in this
example has a traditional argument and an ``input`` argument.  The ``input``
argument is created using the driver ``$driver->input(entityclass)`` method and
has two optional arguments.  The ``resolve`` method passes the ``args`` to
a function that will do the work.  In this example that function returns an
``Artist`` entity thereby allowing a query on the result.


Calling Mutations
-----------------

.. code-block:: php
  :linenos:

  <?php

  $query = 'mutation {
      mutationName(id: 1, input: { name: "newName" }) {
          id
          name
      }
  }';

To call a mutation you must prefix the request with ``mutation``.  The mutation
will then take input from the ``args`` array.  The ``id`` and ``name`` in this
mutation will return the new values from the mutated entity.


Input Argument
--------------

The driver function ``$driver->input(Entity:class)`` will return an
``InputObjectType`` with all the fields set to nonNull, thereby making them
required.  Since this is rarely what is intended, there are two optional
parameters to specify required and optional fields.

.. code-block:: php
  :linenos:

  <?php

  $driver->input(Entity::class, ['requiredField'], ['optionalField'])

In the above mutation example the ``name`` field is required and there are no
optional fields, so the only field in the ``input`` args will be ``name``.
The ``name`` input field will be typed according to its metadata configuration.