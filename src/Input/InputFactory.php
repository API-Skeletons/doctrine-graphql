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
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

use function in_array;

class InputFactory extends AbstractContainer
{
    public function __construct(
        protected Config $config,
        protected EntityManager $entityManager,
        protected TypeManager $typeManager,
        protected Metadata $metadata
    ) {
    }

    /**
    /**
     * @param ...$params
     *      [0] string[] $requiredFields An optional list of just the required fields you want for the mutation.
     *                              This allows specific fields per mutation.
     *      [1] string[] $optionalFields An optional list of optional fields you want for the mutation.
     *                              This allows specific fields per mutation.
     * @throws Error
     */
    public function get(string $id, ...$params): InputObjectType
    {
        $requiredFields = $params[0] ?: [];
        $optionalFields = $params[1] ?: [];

        $targetEntity = $this->metadata->get($id);

        return new InputObjectType([
            'name' => $targetEntity->getTypeName() . '_Input',
            'description' => $targetEntity->getDescription(),
            'fields' => function () use ($id, $targetEntity, $requiredFields, $optionalFields): array {
                $fields = [];

                foreach ($this->entityManager->getClassMetadata($id)->getFieldNames() as $fieldName) {
                    /**
                     * Do not include identifiers as input.  In the majority of cases there will be
                     * no reason to set or update an identifier.  For the case where an identifier
                     * should be set or updated, this facotry is not the correct solution.
                     */
                    if ($optionalFields) {
                        // Include field as optional
                        if (in_array($fieldName, $optionalFields) || $optionalFields === ['*']) {
                            if ($optionalFields === ['*'] && $this->entityManager->getClassMetadata($id)->isIdentifier($fieldName)) {
                                continue;
                            }

                            $fields[$fieldName]['description'] = $targetEntity->getMetadataConfig()['fields'][$fieldName]['description'];
                            $fields[$fieldName]['type']        = $this->typeManager->get($targetEntity->getMetadataConfig()['fields'][$fieldName]['type']);
                        }
                    } elseif ($requiredFields) {
                        // Include fields as required
                        if (in_array($fieldName, $requiredFields) || $requiredFields === ['*']) {
                            if ($requiredFields === ['*'] && $this->entityManager->getClassMetadata($id)->isIdentifier($fieldName)) {
                                continue;
                            }

                            if ($this->entityManager->getClassMetadata($id)->isIdentifier($fieldName)) {
                                throw new Exception('Identifier ' . $fieldName . ' is an invalid input.');
                            }

                            $fields[$fieldName]['description'] = $targetEntity->getMetadataConfig()['fields'][$fieldName]['description'];
                            $fields[$fieldName]['type']        = Type::nonNull($this->typeManager->get($targetEntity->getMetadataConfig()['fields'][$fieldName]['type']));
                        }
                    } else {
                        // All fields are required
                        if ($this->entityManager->getClassMetadata($id)->isIdentifier($fieldName)) {
                            continue;
                        }

                        $fields[$fieldName]['description'] = $targetEntity->getMetadataConfig()['fields'][$fieldName]['description'];
                        $fields[$fieldName]['type']        = Type::nonNull($this->typeManager->get($targetEntity->getMetadataConfig()['fields'][$fieldName]['type']));
                    }
                }

                return $fields;
            },
        ]);
    }
}
