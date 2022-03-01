<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Resolve;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Type\Entity;
use ApiSkeletons\Doctrine\GraphQL\Type\TypeManager;
use Closure;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use GraphQL\Type\Definition\ResolveInfo;

use function strrpos;
use function substr;

class ResolveCollectionFactory
{
    protected EntityManager $entityManager;

    protected Config $config;

    protected FieldResolver $fieldResolver;

    protected TypeManager $typeManager;

    public function __construct(
        EntityManager $entityManager,
        Config $config,
        FieldResolver $fieldResolver,
        TypeManager $typeManager
    ) {
        $this->entityManager = $entityManager;
        $this->config        = $config;
        $this->fieldResolver = $fieldResolver;
        $this->typeManager   = $typeManager;
    }

    public function parseValue(ClassMetadata $metadata, string $field, mixed $value): mixed
    {
        $fieldMapping = $metadata->getFieldMapping($field);
        $graphQLType = $this->typeManager->get($fieldMapping['type']);

        return $graphQLType->parseValue($value);
    }

    public function get(Entity $entity): Closure
    {
        return function ($source, $args, $context, ResolveInfo $resolveInfo) {
            $fieldResolver = $this->fieldResolver;
            $collection    = $fieldResolver($source, $args, $context, $resolveInfo);

            $collectionMetadata = $this->entityManager->getMetadataFactory()
                ->getMetadataFor(
                    $this->entityManager->getMetadataFactory()
                        ->getMetadataFor(ClassUtils::getRealClass($source::class))
                        ->getAssociationTargetClass($resolveInfo->fieldName)
                );

            $criteria = Criteria::create();
            $orderBy  = [];

            $filter = $args['filter'] ?? [];

            $limit  = $this->config->getLimit();

            foreach ($filter as $field => $value) {
                if ($field === '_skip') {
                    $skip = $value;

                    continue;
                }

                if ($field === '_limit') {
                    if ($value <= $limit) {
                        $limit = $value;

                        continue;
                    }
                }

                // Handle other fields as $field_$type: $value
                // Get right-most _text
                $filter = substr($field, strrpos($field, '_') + 1);

                if (strrpos($field, '_') === false) {
                    // Special case for eq `field: value`
                    $value = $this->parseValue($collectionMetadata, $field, $value);
                    $criteria->andWhere($criteria->expr()->eq($field, $value));
                } else {
                    $field = substr($field, 0, strrpos($field, '_'));

                    // Format value type - this seems like something which should
                    // be done in GraphQL.
                    switch ($filter) {
                        case 'eq':
                        case 'neq':
                        case 'lt':
                        case 'lte':
                        case 'gt':
                        case 'gte':
                        case 'contains':
                        case 'startswith':
                        case 'endswith':
                        case 'in':
                        case 'notin':
                            $value = $this->parseValue($collectionMetadata, $field, $value);
                            $criteria->andWhere($criteria->expr()->$filter($field, $value));
                            break;
                        case 'isnull':
                            $value = $this->parseValue($collectionMetadata, $field, $value);
                            $criteria->andWhere($criteria->expr()->$filter($field, $value));
                            break;
                        case 'between':
                            $valueFrom = $this->parseValue($collectionMetadata, $field, $value['from']);
                            $valueTo = $this->parseValue($collectionMetadata, $field, $value['to']);

                            $criteria->andWhere($criteria->expr()->gte($field, $valueFrom));
                            $criteria->andWhere($criteria->expr()->lte($field, $valueTo));
                            break;
                        case 'sort':
                            $orderBy[$field] = $value;
                            break;
                        default:
                            break;
                    }
                }
            }

            $criteria->orderBy($orderBy);

            // Return entities
            return $collection->matching($criteria);
        };
    }
}
