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
use ReflectionProperty;

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
        Assertion::propertyExists($entityClass, $propertyName);

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
            $this->propertyReflection = new ReflectionProperty($this->entityClass, $this->propertyName);
            $this->propertyReflection->setAccessible(true);
        }

        return $this->propertyReflection;
    }
}
