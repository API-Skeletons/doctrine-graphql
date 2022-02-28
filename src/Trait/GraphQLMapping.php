<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Trait;

use ApiSkeletons\Doctrine\GraphQL\Type\DateTime;
use ApiSkeletons\Doctrine\GraphQL\Type\Manager as TypeManager;
use GraphQL\Type\Definition\Type;

trait GraphQLMapping
{
    protected function mapFieldType(string $fieldType): mixed
    {
        static $dateTime;

        if (! $dateTime) {
            $dateTime = new DateTime();
        }

        switch ($fieldType) {
            case 'tinyint':
            case 'smallint':
            case 'integer':
            case 'int':
                $graphQLType = Type::int();
                break;
            case 'boolean':
                $graphQLType = Type::boolean();
                break;
            case 'decimal':
            case 'float':
                $graphQLType = Type::float();
                break;
            case 'bigint':
            case 'string':
            case 'text':
                $graphQLType = Type::string();
                break;
            case 'array':
                $graphQLType = Type::listOf(Type::string());
                break;
            default:
                return $this->driver->getTypeManager()->get($fieldType);
        }

        return $graphQLType;
    }
}
