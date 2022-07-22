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

use GraphQL\Type\Definition\Type;
use function strrpos;
use function substr;

class ResolveCollectionFactory
{
    public function __construct(
        protected EntityManager $entityManager,
        protected Config $config,
        protected FieldResolver $fieldResolver,
        protected TypeManager $typeManager
    ) {
    }

    public function parseValue(ClassMetadata $metadata, string $field, mixed $value): mixed
    {
        /**
         * @psalm-suppress UndefinedDocblockClass
         */
        $fieldMapping = $metadata->getFieldMapping($field);
        $graphQLType  = $this->typeManager->get($fieldMapping['type']);

        return $graphQLType->parseValue($graphQLType->serialize($value));
    }

    /**
     * @param mixed[] $value
     */
    public function parseArrayValue(ClassMetadata $metadata, string $field, array $value): mixed
    {
        foreach ($value as $key => $val) {
            $value[$key] = $this->parseValue($metadata, $field, $val);
        }

        return $value;
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

            $first       = 0;
            $after       = 0;
            $last        = 0;
            $before      = 0;

            foreach ($filter as $field => $value) {
                // Cursor based pagination
                if ($field === '_first') {
                    $first = $value;
                    continue;
                }
                if ($field === '_after') {
                    $after = (int) base64_decode($value, true) + 1;
                    continue;
                }
                if ($field === '_last') {
                    $last = $value;
                    continue;
                }
                if ($field === '_before') {
                    $before = (int) base64_decode($value, true);
                    continue;
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
                            $value = $this->parseValue($collectionMetadata, $field, $value);
                            $criteria->andWhere($criteria->expr()->$filter($field, $value));
                            break;
                        case 'in':
                        case 'notin':
                            $value = $this->parseArrayValue($collectionMetadata, $field, $value);
                            $criteria->andWhere($criteria->expr()->$filter($field, $value));
                            break;
                        case 'isnull':
                            $criteria->andWhere($criteria->expr()->$filter($field));
                            break;
                        case 'between':
                            $value = $this->parseArrayValue($collectionMetadata, $field, $value);

                            $criteria->andWhere($criteria->expr()->gte($field, $value['from']));
                            $criteria->andWhere($criteria->expr()->lte($field, $value['to']));
                            break;
                        case 'sort':
                            $orderBy[$field] = $value;
                            break;
                    }
                }
            }

            $criteria->orderBy($orderBy);

            $offset = 0;
            $limit = $this->config->getLimit();
            $adjustedLimit = $first ?: $last ?: 0;
            if ($adjustedLimit < $limit) {
                $limit = $adjustedLimit;
            }

            if ($after) {
                $offset = $after;
            } else if ($before) {
                $offset = $before - $limit;
            }

            if ($offset < 0) {
                $limit += $offset;
                $offset = 0;
            }

            // Get total count from collection then match
            $itemCount = count($collection->matching($criteria));

            if ($last && ! $before) {
                $offset = $itemCount - $last - 1;
            }

            if ($offset) {
                $criteria->setFirstResult($offset);
            }

            if ($limit) {
                $criteria->setMaxResults($limit);
            }

            // Fetch slice of collection
            $items = $collection->matching($criteria);

            $edges = [];
            $index = 0;
            $lastCursor = base64_encode((string) 0);
            foreach ($items as $result) {
                $cursor = base64_encode((string) ($index + $offset));

                $edges[] = [
                    'node' => $result,
                    'cursor' => $cursor,
                ];

                $lastCursor = $cursor;
                $index ++;
            }

            $endCursor = $itemCount ? $itemCount - 1: 0;
            $endCursor = base64_encode((string) $endCursor);

            // Return entities
            return [
                'edges' => $edges,
                'totalCount' => $itemCount,
                'pageInfo' => [
                    'endCursor' => $endCursor,
                    'hasNextPage' => $endCursor !== $lastCursor,
                ],
            ];
        };
    }
}
