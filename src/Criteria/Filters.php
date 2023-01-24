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

    /** @return string[] */
    public static function toArray(): array
    {
        return [
            self::EQ,
            self::NEQ,
            self::LT,
            self::LTE,
            self::GT,
            self::GTE,
            self::BETWEEN,
            self::CONTAINS,
            self::STARTSWITH,
            self::ENDSWITH,
            self::IN,
            self::NOTIN,
            self::ISNULL,
            self::SORT,
        ];
    }

    /** @return string[] */
    public static function getDescriptions(): array
    {
        return [
            self::EQ         => 'Equals. DateTime not supported.',
            self::NEQ        => 'Not equals',
            self::LT         => 'Less than',
            self::LTE        => 'Less than or equals',
            self::GT         => 'Greater than',
            self::GTE        => 'Greater than or equals',
            self::BETWEEN    => 'Is between from and to inclusive of from and to.  Good substitute for DateTime Equals.',
            self::CONTAINS   => 'Contains the value.  Strings only.',
            self::STARTSWITH => 'Starts with the value.  Strings only.',
            self::ENDSWITH   => 'Ends with the value.  Strings only.',
            self::IN         => 'In the list of values as an array',
            self::NOTIN      => 'Not in the list of values as an array',
            self::ISNULL     => 'Takes a boolean.  If TRUE return results where the field is null. '
                . 'If FALSE returns results where the field is not null. '
                . 'Acts as "isEmpty" for collection filters.  A value of false will '
                . 'be handled as though it were null.',
            self::SORT       => 'Sort the result.  Either "asc" or "desc".',
        ];
    }
}
