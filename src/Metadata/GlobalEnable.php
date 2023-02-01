<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Metadata;

use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Hydrator\Strategy;
use Doctrine\ORM\EntityManager;

use function in_array;

final class GlobalEnable extends AbstractMetadataFactory
{
    /** @var mixed[] */
    private array $metadataConfig = [];

    public function __construct(
        private EntityManager $entityManager,
        protected Config $config,
    ) {
    }

    /**
     * @param string[] $entityClasses
     *
     * @return mixed[]
     */
    public function __invoke(array $entityClasses): array
    {
        $globalIgnore = $this->config->getGlobalIgnore();

        foreach ($entityClasses as $entityClass) {
            // Get extract by value or reference
            $byValue = $this->config->getGlobalByValue() ?? true;

            // Save entity-level metadata
            $this->metadataConfig[$entityClass] = [
                'entityClass' => $entityClass,
                'byValue' => $byValue,
                'namingStrategy' => null,
                'fields' => [],
                'filters' => [],
                'excludeCriteria' => [],
                'description' => $entityClass,
                'typeName' => $this->getTypeName($entityClass),
            ];

            // Fetch fields
            $entityClassMetadata = $this->entityManager->getMetadataFactory()->getMetadataFor($entityClass);
            $fieldNames          = $entityClassMetadata->getFieldNames();

            foreach ($fieldNames as $fieldName) {
                if (in_array($fieldName, $globalIgnore)) {
                    continue;
                }

                $this->metadataConfig[$entityClass]['fields'][$fieldName]['description'] =
                    $fieldName;

                $this->metadataConfig[$entityClass]['fields'][$fieldName]['type'] =
                    $entityClassMetadata->getTypeOfField($fieldName);

                // Set default strategy based on field type
                $this->metadataConfig[$entityClass]['fields'][$fieldName]['strategy'] =
                    $this->getDefaultStrategy($entityClassMetadata->getTypeOfField($fieldName));

                $this->metadataConfig[$entityClass]['fields'][$fieldName]['excludeCriteria'] = [];
            }

            // Fetch attributes for associations
            $associationNames = $this->entityManager->getMetadataFactory()
                ->getMetadataFor($entityClass)->getAssociationNames();

            foreach ($associationNames as $associationName) {
                if (in_array($associationName, $globalIgnore)) {
                    continue;
                }

                $this->metadataConfig[$entityClass]['fields'][$associationName]['excludeCriteria']         = [];
                $this->metadataConfig[$entityClass]['fields'][$associationName]['description']             = $associationName;
                $this->metadataConfig[$entityClass]['fields'][$associationName]['filterCriteriaEventName']
                    = null;

                // NullifyOwningAssociation is not used for globalEnable
                $this->metadataConfig[$entityClass]['fields'][$associationName]['strategy'] =
                    Strategy\AssociationDefault::class;
            }
        }

        return $this->metadataConfig;
    }
}
