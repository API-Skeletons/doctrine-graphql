<?php
declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Type;

use DateTime as PHPDateTime;
use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;

final class DateTime extends ScalarType
{
    /**
     * @var string
     */
    public $description =
    'The `DateTime` scalar type represents datetime data.
The format is ISO-8601 e.g. 2004-02-12T15:19:21+00:00';

    /**
     * @codeCoverageIgnore
     */
    public function parseLiteral(Node $valueNode, ?array $variables = null)
    {
        if (! $valueNode instanceof StringValueNode) {
            throw new Error('Query error: Can only parse strings got: ' . $valueNode->kind, $valueNode);
        }

        return $valueNode->value;
    }

    public function parseValue($value)
    {
        if (! is_string($value)) {
            throw new \UnexpectedValueException('Date is not a string: ' . $value);
        }

        return PHPDateTime::createFromFormat('c', $value);
    }

    public function serialize($value)
    {
        if ($value instanceof PHPDateTime) {
            $value = $value->format('c');
        }

        return $value;
    }
}
