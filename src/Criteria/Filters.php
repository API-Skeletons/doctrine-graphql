<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Criteria;

final class Filters
{
    public const EQ         = 'eq';
    public const NEQ        = 'neq';
    public const LT         = 'lt';
    public const LTE        = 'lte';
    public const GT         = 'gt';
    public const GTE        = 'gte';
    public const BETWEEN    = 'between';
    public const CONTAINS   = 'contains';
    public const STARTSWITH = 'startswith';
    public const ENDSWITH   = 'endswith';
    public const IN         = 'in';
    public const NOTIN      = 'notin';
    public const ISNULL     = 'isnull';
    public const SORT       = 'sort';
}
