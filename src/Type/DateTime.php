<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use DateTime as PHPDateTime;
use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

use function is_string;

class DateTime extends ScalarType
{
    // phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
    public $description = 'The `DateTime` scalar type represents datetime data.'
    . 'The format is ISO-8601 e.g. 2004-02-12T15:19:21+00:00';

    public function parseLiteral(Node $valueNode, ?array $variables = null): string
    {
        // @codeCoverageIgnoreStart
        if (! $valueNode instanceof StringValueNode) {
            throw new Error('Query error: Can only parse strings got: ' . $valueNode->kind, $valueNode);
        }

        // @codeCoverageIgnoreEnd

        return $valueNode->value;
    }

    public function parseValue(mixed $value): PHPDateTime
    {
        if (! is_string($value)) {
            throw new Error('Date is not a string: ' . $value);
        }

        return PHPDateTime::createFromFormat('Y-m-d\TH:i:sP', $value);
    }

    public function serialize(mixed $value): ?string
    {
        if ($value instanceof PHPDateTime) {
            $value = $value->format('c');
        }

        return $value;
    }
}
