<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;

use function is_string;
use function json_decode;
use function json_encode;

class Json extends ScalarType
{
    // phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
    public string|null $description = 'The `JSON` scalar type represents json data.';

    public function parseLiteral(Node $valueNode, array|null $variables = null): string
    {
        throw new Error('JSON fields are not searchable', $valueNode);
    }

    /**
     * @return mixed[]|null
     *
     * @throws Error
     */
    public function parseValue(mixed $value): array|null
    {
        if (! is_string($value)) {
            throw new Error('Json is not a string: ' . $value);
        }

        return json_decode($value, true);
    }

    public function serialize(mixed $value): string|null
    {
        return json_encode($value);
    }
}
