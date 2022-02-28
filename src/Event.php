<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL;

class Event
{
    /**
     * Fired as soon as resolve is called.  Can override entire
     * resolve function.
     */
    public const RESOLVE = 'resolve';

    /**
     * After the generated resolve has finished this allows
     * you to return a different value such as rewriting the
     * data.
     */
    public const RESOLVE_POST = 'resolvePost';

    /**
     * A security event, use this to add filters to the
     * queryBuilder before it is used to find the entities
     * the user requested.
     */
    public const FILTER_QUERY_BUILDER = 'filterQueryBuilder';

    /**
     * Fired everytime a fieldType is resolved to a GraphQL
     * type.  Allows overriding the GraphQL field type.
     */
    public const MAP_FIELD_TYPE = 'mapFieldType';
}
