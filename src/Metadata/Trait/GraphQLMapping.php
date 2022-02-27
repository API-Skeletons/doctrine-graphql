<?php

namespace ApiSkeletons\Doctrine\GraphQL\Metadata\Trait;

use ApiSkeletons\Doctrine\GraphQL\Type\DateTime;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\Type;

trait GraphQLMapping
{
    protected function mapFieldType(string $fieldType)
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
            case 'datetime':
                $graphQLType = Type::string();
//                $graphQLType = $dateTime;
                break;
            default:
                throw new Error('GraphQL Type not found for type ' . $fieldType);
        }

        return $graphQLType;
    }
}
