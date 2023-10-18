<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable;

use Assert\Assertion;
use FSi\Component\Translatable\Exception\ClassDoesNotExistException;
use ReflectionClass;

use function array_walk;
use function class_exists;

final class TranslationConfiguration
{
    /**
     * @var class-string<object>
     */
    private string $entityClass;
    private string $localeField;
    private string $relationField;
    private ?PropertyConfiguration $localePropertyReflection;
    private ?PropertyConfiguration $relationPropertyReflection;
    /**
     * @var array<string, PropertyConfiguration>
     */
    private array $propertyConfigurations;

    /**
     * @param class-string<object> $entityClass
     * @param list<string> $properties
     */
    public function __construct(
        string $entityClass,
        string $localeField,
        string $relationField,
        array $properties
    ) {
        $this->assertValidClassAndLocaleField($entityClass, $localeField);

        $this->entityClass = $entityClass;
        $this->localeField = $localeField;
        $this->relationField = $relationField;
        $this->localePropertyReflection = null;
        $this->relationPropertyReflection = null;
        array_walk(
            $properties,
            function (string $property) use ($entityClass): void {
                $configuration = new PropertyConfiguration($entityClass, $property);
                $this->propertyConfigurations[$property] = $configuration;
            }
        );
    }

    public function creatNewEntityInstance(): object
    {
        return (new ReflectionClass($this->entityClass))->newInstance();
    }

    /**
     * @return class-string<object>
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getLocaleField(): string
    {
        return $this->localeField;
    }

    public function getRelationField(): string
    {
        return $this->relationField;
    }

    public function getLocaleForEntity(object $entity): ?string
    {
        return $this->getLocaleReflectionForProperty()->getValueForEntity($entity);
    }

    public function setLocaleForEntity(object $entity, string $locale): void
    {
        $this->getLocaleReflectionForProperty()->setValueForEntity($entity, $locale);
    }

    public function getRelationValueForEntity(object $entity): ?object
    {
        return $this->getRelationReflectionForProperty()->getValueForEntity($entity);
    }

    public function setRelationValueForEntity(object $entity, object $relation): void
    {
        $this->getRelationReflectionForProperty()->setValueForEntity($entity, $relation);
    }

    /**
     * @return mixed
     */
    public function getValueForProperty(object $entity, string $property)
    {
        Assertion::keyExists($this->propertyConfigurations, $property);
        return $this->propertyConfigurations[$property]->getValueForEntity($entity);
    }

    /**
     * @param mixed $value
     */
    public function setValueForProperty(object $entity, string $property, $value): void
    {
        Assertion::keyExists($this->propertyConfigurations, $property);
        $this->propertyConfigurations[$property]->setValueForEntity($entity, $value);
    }

    /**
     * @return array<string, PropertyConfiguration>
     */
    public function getPropertyConfigurations(): array
    {
        return $this->propertyConfigurations;
    }

    private function getLocaleReflectionForProperty(): PropertyConfiguration
    {
        if (null === $this->localePropertyReflection) {
            $this->localePropertyReflection = new PropertyConfiguration(
                $this->entityClass,
                $this->localeField
            );
        }

        return $this->localePropertyReflection;
    }

    private function getRelationReflectionForProperty(): PropertyConfiguration
    {
        if (null === $this->relationPropertyReflection) {
            $this->relationPropertyReflection = new PropertyConfiguration(
                $this->entityClass,
                $this->relationField
            );
        }

        return $this->relationPropertyReflection;
    }

    private function assertValidClassAndLocaleField(
        string $entityClass,
        string $localeField
    ): void {
        if (false === class_exists($entityClass)) {
            throw ClassDoesNotExistException::create($entityClass);
        }

        PropertyConfiguration::verifyPropertyExists($entityClass, $localeField);
    }
}
