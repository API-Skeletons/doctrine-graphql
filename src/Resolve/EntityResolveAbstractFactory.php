<?php

namespace ApiSkeletons\Doctrine\GraphQL\Resolve;

use ApiSkeletons\Doctrine\Criteria\Filter\Service\FilterManager as CriteriaFilterManager;
use ApiSkeletons\Doctrine\Criteria\Builder as CriteriaBuilder;
use ApiSkeletons\Doctrine\GraphQL\AbstractAbstractFactory;
use ApiSkeletons\Doctrine\GraphQL\Event;
use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ApiTools\Doctrine\QueryBuilder\Filter\Service\ORMFilterManager;
use Laminas\ApiTools\Doctrine\QueryBuilder\OrderBy\Service\ORMOrderByManager;

final class EntityResolveAbstractFactory extends AbstractAbstractFactory implements
    AbstractFactoryInterface
{
    /**
     * @codeCoverageIgnore
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        return $this->canCreate($services, $requestedName);
    }

    /**
     * @codeCoverageIgnore
     */
    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        return $this($services, $requestedName);
    }

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $hydratorManager = $container->get('HydratorManager');
        $hydratorAlias = 'ApiSkeletons\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $requestedName);

        return $hydratorManager->has($hydratorAlias);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Closure
    {
        // @codeCoverageIgnoreStart
        if ($this->isCached($requestedName, $options)) {
            return $this->getCache($requestedName, $options);
        }
        // @codeCoverageIgnoreEnd

        parent::__invoke($container, $requestedName, $options);

        $config = $container->get('config');
        $hydratorAlias = 'ApiSkeletons\\Doctrine\\GraphQL\\Hydrator\\' . str_replace('\\', '_', $requestedName);
        $hydratorExtractTool = $container->get('ApiSkeletons\\Doctrine\\GraphQL\\Hydrator\\HydratorExtractTool');
        $filterManager = $container->get(ORMFilterManager::class);
        $orderByManager = $container->get(ORMOrderByManager::class);
        $criteriaFilterManager = $container->get(CriteriaFilterManager::class);
        $criteriaBuilder = $container->get(CriteriaBuilder::class);
        // @codingStandardsIgnoreStart
        $objectManager = $container
            ->get(
                $config['apiskeletons-doctrine-graphql-hydrator'][$hydratorAlias][$options['hydrator_section']]['object_manager']
            );
        // @codingStandardsIgnoreEnd

        $instance = function (
            $obj,
            $args,
            $context,
            ResolveInfo $info
        ) use (
            $options,
            $hydratorAlias,
            $hydratorExtractTool,
            $objectManager,
            $requestedName,
            $filterManager,
            $orderByManager,
            $criteriaBuilder
        ) {

            // Allow listener to resolve function
            $results = $this->getEventManager()->trigger(
                Event::RESOLVE,
                $this,
                [
                    'object' => $obj,
                    'arguments' => $args,
                    'context' => $context,
                    'hydratorAlias' => $hydratorAlias,
                    'objectManager' => $objectManager,
                    'entityClassName' => $requestedName,
                ]
            );
            if ($results->stopped()) {
                return $results->last();
            }

            if ($context->getUsePartials()) {
                // Select only the fields being queried
                $fieldArray = $info->getFieldSelection();

                // Add primary key of this entity; required for partials
                $meta = $objectManager->getClassMetadata($requestedName);
                $fieldArray[$meta->getSingleIdentifierFieldName()] = 1;

                // Verify all fields exist and only query for scalar values, not relations
                foreach ($fieldArray as $fieldName => $value) {
                    if ($meta->hasAssociation($fieldName)) {
                        unset($fieldArray[$fieldName]);
                    }
                }
                $fieldList = implode(',', array_keys($fieldArray));

                // Build query builder from Query Provider
                $queryBuilder = ($objectManager->createQueryBuilder())
                    ->select('partial row.{' . $fieldList . '}')
                    ->from($requestedName, 'row')
                    ;
            } else {
                // Build query builder from Query Provider
                $queryBuilder = ($objectManager->createQueryBuilder())
                    ->select('row')
                    ->from($requestedName, 'row')
                    ;
            }

            $this->getEventManager()->trigger(
                Event::FILTER_QUERY_BUILDER,
                $this,
                [
                    'object' => $obj,
                    'arguments' => $args,
                    'context' => $context,
                    'objectManager' => $objectManager,
                    'queryBuilder' => $queryBuilder,
                    'entityClassName' => $requestedName,
                ]
            );

            // Resolve top level filters
            $filter = $args['filter'] ?? [];
            $filterArray = [];
            $orderByArray = [];
            $criteriaArray = [];
            $distinctField = null;
            $skip = 0;
            $limit = $options['limit'];

            foreach ($filter as $field => $value) {
                // Command fields
                if ($field == '_skip') {
                    $skip = $value;
                    continue;
                }

                if ($field == '_limit') {
                    if ($value <= $options['limit']) {
                        $limit = $value;
                    }
                    continue;
                }

                // Handle most fields as $field_$type: $value
                // Get right-most _text
                $filter = substr($field, strrpos($field, '_') + 1);
                if (strpos($field, '_') === false || ! $this->isFilter($filter)) {
                    // Handle field:value
                     $filterArray[] = [
                         'type' => 'eq',
                         'field' => $field,
                         'value' => $value,
                     ];
                } elseif (strpos($field, '_') !== false && $this->isFilter($filter)) {
                    $field = substr($field, 0, (int)strrpos($field, '_'));

                    switch ($filter) {
                        case 'sort':
                            $orderByArray[] = [
                                'type' => 'field',
                                'field' => $field,
                                'direction' => $value,
                            ];
                            break;
                        case 'contains':
                            $filterArray[] = [
                                'type' => 'like',
                                'field' => $field,
                                'value' => '%' . $value . '%',
                            ];
                            break;
                        case 'startswith':
                            $filterArray[] = [
                                'type' => 'like',
                                'field' => $field,
                                'value' => $value . '%',
                            ];
                            break;
                        case 'endswith':
                            $filterArray[] = [
                                'type' => 'like',
                                'field' => $field,
                                'value' => '%' . $value,
                            ];
                            break;
                        case 'between':
                            $value['type'] = $filter;
                            $value['field'] = $field;
                            $filterArray[] = $value;
                            break;
                        case 'in':
                            $filterArray[] = [
                                'type' => 'in',
                                'field' => $field,
                                'values' => $value,
                            ];
                            break;
                        case 'notin':
                            $filterArray[] = [
                                'type' => 'notin',
                                'field' => $field,
                                'values' => $value,
                            ];
                            break;
                        case 'isnull':
                            if ($value === true) {
                                $filterArray[] = [
                                    'type' => 'isnull',
                                    'field' => $field,
                                    'values' => null,
                                ];
                            } else {
                                $filterArray[] = [
                                    'type' => 'isnotnull',
                                    'field' => $field,
                                    'values' => null,
                                ];
                            }
                            break;
                        case 'distinct':
                            if (! $distinctField && $value) {
                                $distinctField = $field;
                            }
                            break;
                        case 'memberof':
                            $criteriaArray[] = [
                                'type' => 'memberof',
                                'field' => $field,
                                'value' => $value,
                            ];
                            break;
                        default:
                            $filterArray[] = [
                                'type' => $filter,
                                'field' => $field,
                                'value' => $value,
                            ];
                            break;
                    }
                }
            }

            // Process fitlers through filter manager
            $metadata = $objectManager->getClassMetadata($requestedName);
            if ($filterArray) {
                foreach ($filterArray as $key => $filter) {
                    $filterArray[$key]['format'] = 'Y-m-d\TH:i:sP';
                }

                $filterManager->filter(
                    $queryBuilder,
                    $metadata,
                    $filterArray
                );
            }
            if ($orderByArray) {
                $orderByManager->orderBy(
                    $queryBuilder,
                    $metadata,
                    $orderByArray
                );
            }
            if ($skip) {
                $queryBuilder->setFirstResult($skip);
            }
            if ($limit) {
                $queryBuilder->setMaxResults($limit);
            }

            // Fetch from Query Builder
            $results = $queryBuilder->getQuery()->getResult();

            // Build hydrated result collection
            $resultCollection = $hydratorExtractTool->extractToCollection($results, $hydratorAlias, $options);

            // Criteria post filter
            if ($criteriaArray) {
                $criteria = $criteriaBuilder->create($metadata, $criteriaArray, []);
                $resultCollection = $resultCollection->matching($criteria);
            }

            // Distinct post filter
            if ($distinctField) {
                $distinctValueCollection = new ArrayCollection();
                foreach ($resultCollection as $key => $value) {
                    if (! $distinctValueCollection->contains($value[$distinctField])) {
                        $distinctValueCollection->add($value[$distinctField]);
                    } else {
                        $resultCollection->remove($key);
                    }
                }
            }

            // Allow listener to resolve post function
            $results = $this->getEventManager()->trigger(
                Event::RESOLVE_POST,
                $this,
                [
                    'object' => $obj,
                    'arguments' => $args,
                    'context' => $context,
                    'resultCollection' => $resultCollection,
                    'hydratorAlias' => $hydratorAlias,
                    'objectManager' => $objectManager,
                    'queryBuilder' => $queryBuilder,
                    'entityClassName' => $requestedName,
                ]
            );
            if ($results->stopped()) {
                return $results->last();
            }

            return $resultCollection->toArray();
        };

        return $this->cache($requestedName, $options, $instance);
    }
}
