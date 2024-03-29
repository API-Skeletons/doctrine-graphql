<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;

use GraphQL\Error\Error;
use Laminas\Hydrator\Strategy\StrategyInterface;

/**
 * Nullify an association.
 *
 * In a many to many relationship from a known starting point it is possible
 * to backwards-query the owning relationship to gather data the user should
 * not be privileged to.
 *
 * For instance in a User <> Role relationship a user may have many roles.  But
 * a role may have many users.  So in a query where a user is fetched then their
 * roles are fetched you could then reverse the query to fetch all users with the
 * same role
 *
 * This query would return all user names with the same roles as the user who
 * created the artist.
 * { artist { user { role { user { name } } } } }
 *
 * This hydrator strategy is used to prevent the reverse lookup by nullifying
 * the response when queried from the owning side of a many to many relationship
 *
 * Ideally the developer will add the owning relation to a filter so the
 * field is not queryable at all.  This strategy exists as a patch for generating
 * a configuration skeleton.
 */
class NullifyOwningAssociation extends AbstractCollectionStrategy implements
    StrategyInterface
{
    public function extract(mixed $value, object|null $object = null): mixed
    {
        throw new Error('Query is barred by Nullify Owning Association');
    }

    /**
     * @param mixed[]|null $data
     *
     * @codeCoverageIgnore
     */
    public function hydrate(mixed $value, array|null $data): void
    {
    }
}
