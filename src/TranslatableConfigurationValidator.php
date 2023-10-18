<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable;

use FSi\Component\Translatable\Exception\MismatchedFieldTypeDeclarationException;
use FSi\Component\Translatable\Exception\MismatchedNullableFieldTypeDeclarationException;
use FSi\Component\Translatable\Exception\MismatchedUndefinedFieldTypeDeclarationException;
use FSi\Component\Translatable\Exception\MissingTranslationProperty;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

use function array_key_exists;

final class TranslatableConfigurationValidator
{
    public static function validate(TranslatableConfiguration $configuration): void
    {
        $translatableClass = $configuration->getEntityClass();
        $translationClass = $configuration->getEntityClass();
        $translatableConfigurations = $configuration->getPropertyConfigurations();
        $translationConfigurations = $configuration
            ->getTranslationConfiguration()
            ->getPropertyConfigurations()
        ;

        array_walk(
            $translatableConfigurations,
            function (
                PropertyConfiguration $translatableConfiguration,
                string $propertyName
            ) use (
                $translationConfigurations,
                $translatableClass,
                $translationClass
            ): void {
                if (false === array_key_exists($propertyName, $translationConfigurations)) {
                    throw MissingTranslationProperty::create($translationClass, $propertyName);
                }

                $translatableType = $translatableConfiguration->getType();
                $translationType = $translationConfigurations[$propertyName]->getType();
                if (null === $translatableType && null === $translationType) {
                    return;
                }

                if (null === $translatableType || null === $translationType) {
                    throw MismatchedUndefinedFieldTypeDeclarationException::create(
                        $translatableClass,
                        $translationClass,
                        $propertyName
                    );
                }

                self::assertPropertyType(
                    $translatableType,
                    $translationType,
                    $translatableClass,
                    $translationClass,
                    $propertyName
                );
            }
        );
    }

    /**
     * @param ReflectionNamedType|ReflectionUnionType|ReflectionType $translatableTypeReflection
     * @param ReflectionNamedType|ReflectionUnionType|ReflectionType $translationTypeReflection
     */
    private static function assertPropertyType(
        $translatableTypeReflection,
        $translationTypeReflection,
        string $translatableClass,
        string $translationClass,
        string $propertyName
    ): void {
        if (true === $translatableTypeReflection instanceof ReflectionNamedType) {
            self::validateNamedType(
                $translatableTypeReflection,
                $translationTypeReflection,
                $translatableClass,
                $translationClass,
                $propertyName
            );
        } elseif (true === $translatableTypeReflection instanceof ReflectionUnionType) {
            self::validateUnionType(
                $translatableTypeReflection,
                $translationTypeReflection,
                $translatableClass,
                $translationClass,
                $propertyName
            );
        }
    }

    /**
     * @param ReflectionNamedType|ReflectionUnionType|ReflectionType $translationTypeReflection
     * @throws MismatchedFieldTypeDeclarationException
     */
    private static function validateNamedType(
        ReflectionNamedType $translatableTypeReflection,
        $translationTypeReflection,
        string $translatableClass,
        string $translationClass,
        string $propertyName
    ): void {
        if (false === $translationTypeReflection instanceof ReflectionNamedType) {
            throw MismatchedFieldTypeDeclarationException::create(
                $translatableClass,
                $translationClass,
                $propertyName
            );
        }

        if ($translatableTypeReflection->allowsNull() !== $translationTypeReflection->allowsNull()) {
            throw MismatchedNullableFieldTypeDeclarationException::create(
                $translatableClass,
                $translationClass,
                $propertyName
            );
        }

        $actualTranslatableType = self::sanitizePropertyType($translatableTypeReflection->getName());
        $actualTranslationType = self::sanitizePropertyType($translationTypeReflection->getName());
        if ($actualTranslatableType !== $actualTranslationType) {
            throw MismatchedFieldTypeDeclarationException::create(
                $translatableClass,
                $translationClass,
                $propertyName
            );
        }
    }

    /**
     * @param ReflectionNamedType|ReflectionUnionType|ReflectionType $translationTypeReflection
     * @throws MismatchedFieldTypeDeclarationException
     */
    private static function validateUnionType(
        ReflectionUnionType $translatableTypeReflection,
        $translationTypeReflection,
        string $translatableClass,
        string $translationClass,
        string $propertyName
    ): void {
        if (false === $translationTypeReflection instanceof ReflectionUnionType) {
            throw MismatchedFieldTypeDeclarationException::create(
                $translatableClass,
                $translationClass,
                $propertyName
            );
        }

        if ($translatableTypeReflection->allowsNull() !== $translationTypeReflection->allowsNull()) {
            throw MismatchedNullableFieldTypeDeclarationException::create(
                $translatableClass,
                $translationClass,
                $propertyName
            );
        }

        /** @var list<string> $translatableUnionTypes */
        $translatableUnionTypes = array_map(
            static fn(ReflectionNamedType $propertyTypeReflection): string
                => self::sanitizePropertyType($propertyTypeReflection->getName()),
            $translatableTypeReflection->getTypes()
        );

        /** @var list<string> $translationUnionTypes */
        $translationUnionTypes = array_map(
            static fn(ReflectionNamedType $propertyTypeReflection): string
                => self::sanitizePropertyType($propertyTypeReflection->getName()),
            $translationTypeReflection->getTypes()
        );

        if ([] !== array_diff($translatableUnionTypes, $translationUnionTypes)) {
            throw MismatchedFieldTypeDeclarationException::create(
                $translatableClass,
                $translationClass,
                $propertyName
            );
        }
    }

    private static function sanitizePropertyType(string $propertyType): string
    {
        return ltrim($propertyType, '?');
    }
}
