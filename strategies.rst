Hydrator Strategies
===================

Some hydrator strategies are supplied with this library.  You may also add your own hydrator
strategies if you desire.

Included Hydrator Strategies
----------------------------

* ``ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\FieldDefault`` - This strategy is applied to most 
field values.  It will return the exact value of the field.
* ``ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToInteger`` - This strategy will convert the 
field value to an integer to be handled as an integer internal to PHP.
* ``ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToFloat`` - Similar to ``ToInteger``, this will
convert the field value to a float to be handled as a float internal to PHP.
* ``ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\ToBoolean`` - Similar to ``ToInteger``, this will
convert the field value to a boolean to be handled as a boolean internal to PHP.
* ``ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy\NullifyOwningAssociation`` - This strategy is 
detailed below.


NullifyOwningAssociation
------------------------

Nullify an association.

In a many to many relationship from a known starting point it is possible
to backwards-query the owning relationship to gather data the user should
not be privileged to.

For instance in a User <> Role relationship a user may have many roles.  But
a role may have many users.  So in a query where a user is fetched then their
roles are fetched you could then reverse the query to fetch all users with the
same role

This query would return all user names with the same roles as the user who
created the artist.

``{ artist { user { role { user { name } } } } }``

This hydrator strategy is used to prevent the reverse lookup by nullifying
the response when queried from the owning side of a many to many relationship

Ideally the developer will add the owning relation to a filter so the
field is not queryable at all.
