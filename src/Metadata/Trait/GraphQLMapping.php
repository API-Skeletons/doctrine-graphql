<?php

namespace ApiSkeletons\Doctrine\GraphQL\Metadata\Trait;

use GraphQL\Type\Definition\Type;

trait GraphQLMapping
{
    protected function mapFieldType(string $fieldType)
    {
        $graphQLType = null;

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

                // FIXME:  Add datetime
                break;
        }

        return $graphQLType;
    }
}
