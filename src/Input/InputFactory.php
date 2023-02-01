<?php

declare(strict_types=1);

namespace ApiSkeletons\Doctrine\GraphQL\Input;

use ApiSkeletons\Doctrine\GraphQL\AbstractContainer;
use ApiSkeletons\Doctrine\GraphQL\Config;
use ApiSkeletons\Doctrine\GraphQL\Metadata\Metadata;
use ApiSkeletons\Doctrine\GraphQL\Type\TypeManager;
use Doctrine\ORM\EntityManager;
use Exception;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

use function count;
use function in_array;

class InputFactory extends AbstractContainer
{
    public function __construct(
        protected Config $config,
        protected EntityManager $entityManager,
        protected TypeManager $typeManager,
        protected Metadata $metadata,
    ) {
    }

    /**
     * @param string[]                           $optionalFields
     * @param array<array-key, InputObjectField> $fields
     *
     * @return array<array-key, InputObjectField>
     */
    protected function addOptionalFields(
        mixed $targetEntity,
        array $optionalFields,
        array $fields,
    ): array {
        foreach ($this->entityManager->getClassMetadata($targetEntity->getEntityClass())->getFieldNames() as $fieldName) {
            if (! in_array($fieldName, $optionalFields) && $optionalFields !== ['*']) {
                continue;
            }

            if ($optionalFields === ['*'] && $this->entityManager->getClassMetadata($targetEntity->getEntityClass())->isIdentifier($fieldName)) {
                continue;
            }

            $fields[$fieldName] = new InputObjectField([
                'name' => $fieldName,
                'description' => (string) $targetEntity->getMetadataConfig()['fields'][$fieldName]['description'],
                'type' => $this->typeManager->get($targetEntity->getMetadataConfig()['fields'][$fieldName]['type']),
            ]);
        }

        return $fields;
    }

    /**
     * @param string[]                           $requiredFields
     * @param array<array-key, InputObjectField> $fields
     *
     * @return array<array-key, InputObjectField>
     */
    protected function addRequiredFields(
        mixed $targetEntity,
        array $requiredFields,
        array $fields,
    ): array {
        foreach ($this->entityManager->getClassMetadata($targetEntity->getEntityClass())->getFieldNames() as $fieldName) {
            if (! in_array($fieldName, $requiredFields) && $requiredFields !== ['*']) {
                continue;
            }

            if ($requiredFields === ['*'] && $this->entityManager->getClassMetadata($targetEntity->getEntityClass())->isIdentifier($fieldName)) {
                continue;
            }

            if ($this->entityManager->getClassMetadata($targetEntity->getEntityClass())->isIdentifier($fieldName)) {
                throw new Exception('Identifier ' . $fieldName . ' is an invalid input.');
            }

            $fields[$fieldName] = new InputObjectField([
                'name' => $fieldName,
                'description' => (string) $targetEntity->getMetadataConfig()['fields'][$fieldName]['description'],
                'type' => Type::nonNull($this->typeManager->get($targetEntity->getMetadataConfig()['fields'][$fieldName]['type'])),
            ]);
        }

        return $fields;
    }

    /**
     * @param array<array-key, InputObjectField> $fields
     *
     * @return array<array-key, InputObjectField>
     */
    protected function addAllFieldsAsRequired(mixed $targetEntity, array $fields): array
    {
        foreach ($this->entityManager->getClassMetadata($targetEntity->getEntityClass())->getFieldNames() as $fieldName) {
            if ($this->entityManager->getClassMetadata($targetEntity->getEntityClass())->isIdentifier($fieldName)) {
                continue;
            }

            $fields[$fieldName] = new InputObjectField([
                'name' => $fieldName,
                'description' => (string) $targetEntity->getMetadataConfig()['fields'][$fieldName]['description'],
                'type' => Type::nonNull($this->typeManager->get($targetEntity->getMetadataConfig()['fields'][$fieldName]['type'])),
            ]);
        }

        return $fields;
    }

    /**
     * @param string[] $requiredFields An optional list of just the required fields you want for the mutation.
     *                              This allows specific fields per mutation.
     * @param string[] $optionalFields An optional list of optional fields you want for the mutation.
     *                              This allows specific fields per mutation.
     *
     * @throws Error
     */
    public function get(string $id, array $requiredFields = [], array $optionalFields = []): InputObjectType
    {
        $fields       = [];
        $targetEntity = $this->metadata->get($id);

        /**
         * Do not include identifiers as input.  In the majority of cases there will be
         * no reason to set or update an identifier.  For the case where an identifier
         * should be set or updated, this facotry is not the correct solution.
         */

        if (! count($requiredFields) && ! count($optionalFields)) {
            $fields = $this->addAllFieldsAsRequired($targetEntity, $fields);
        } else {
            $fields = $this->addRequiredFields($targetEntity, $requiredFields, $fields);
            $fields = $this->addOptionalFields($targetEntity, $optionalFields, $fields);
        }

        return new InputObjectType([
            'name' => $targetEntity->getTypeName() . '_Input',
            'description' => $targetEntity->getDescription(),
            'fields' => static fn () => $fields,
        ]);
    }
}
