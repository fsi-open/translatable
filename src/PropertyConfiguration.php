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
use ReflectionClass;
use ReflectionProperty;

use function array_reduce;
use function class_parents;
use function property_exists;

final class PropertyConfiguration
{
    /**
     * @var class-string
     */
    private string $entityClass;
    private string $propertyName;
    private ?ReflectionProperty $propertyReflection;

    /**
     * @param class-string $entityClass
     * @param string $propertyName
     */
    public function __construct(string $entityClass, string $propertyName)
    {
        $this->verifyPropertyExists($entityClass, $propertyName);
        $this->entityClass = $entityClass;
        $this->propertyName = $propertyName;
        $this->propertyReflection = null;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * @param object $entity
     * @return mixed
     */
    public function getValueForEntity(object $entity)
    {
        Assertion::isInstanceOf($entity, $this->entityClass);
        return $this->getPropertyReflection()->getValue($entity);
    }

    /**
     * @param object $entity
     * @param mixed $value
     * @return void
     */
    public function setValueForEntity(object $entity, $value): void
    {
        Assertion::isInstanceOf($entity, $this->entityClass);
        $this->getPropertyReflection()->setValue($entity, $value);
    }

    private function getPropertyReflection(): ReflectionProperty
    {
        if (null === $this->propertyReflection) {
            $reflectionClass = new ReflectionClass($this->entityClass);
            do {
                if (false === $reflectionClass->hasProperty($this->propertyName)) {
                    continue;
                }

                $this->propertyReflection = $reflectionClass->getProperty($this->propertyName);
                $this->propertyReflection->setAccessible(true);
            } while ($reflectionClass = $reflectionClass->getParentClass());
        }

        Assertion::notNull(
            $this->propertyReflection,
            $this->nonExistantFieldExceptionMessage($this->entityClass, $this->propertyName)
        );

        return $this->propertyReflection;
    }

    /**
     * @param class-string $entityClass
     * @param string $propertyName
     * @return void
     */
    private function verifyPropertyExists(string $entityClass, string $propertyName): void
    {
        if (true === property_exists($entityClass, $propertyName)) {
            return;
        }

        $parents = class_parents($entityClass);
        Assertion::isArray($parents, "Unable to read parent classes for \"{$entityClass}\"");

        $propertyExistsInAParent = array_reduce(
            $parents,
            fn(bool $accumulator, string $parent): bool =>
                true === $accumulator || property_exists($parent, $propertyName),
            false
        );

        Assertion::true(
            $propertyExistsInAParent,
            $this->nonExistantFieldExceptionMessage($entityClass, $propertyName)
        );
    }

    private function nonExistantFieldExceptionMessage(string $entityClass, string $propertyName): string
    {
        return "Neiter class \"{$entityClass}\" nor any of it's parent have the property \"{$propertyName}\"";
    }
}
