<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\Component\Translatable;

use Codeception\Test\Unit;
use FSi\Component\Translatable\Exception\ClassDoesNotExistException;
use FSi\Component\Translatable\Exception\MismatchedFieldTypeDeclarationException;
use FSi\Component\Translatable\Exception\MismatchedNullableFieldTypeDeclarationException;
use FSi\Component\Translatable\Exception\MismatchedUndefinedFieldTypeDeclarationException;
use FSi\Component\Translatable\Exception\PropertyDoesNotExistException;
use FSi\Component\Translatable\TranslatableConfiguration;
use stdClass;
use Tests\FSi\App\Entity\InvalidEntity;
use Tests\FSi\App\Entity\InvalidEntityTranslation;

final class TranslatableConfigurationTest extends Unit
{
    public function testUsingNonExistentTranslatableClass(): void
    {
        $this->expectException(ClassDoesNotExistException::class);
        $this->expectExceptionMessage('Class "Some\Random\ClassName" does not exist.');

        /** @var class-string<object> $nonExistentClassString */
        $nonExistentClassString = 'Some\Random\ClassName';
        new TranslatableConfiguration(
            $nonExistentClassString,
            'locale',
            false,
            stdClass::class,
            'locale',
            'translatable',
            []
        );
    }

    public function testNonExistentTranslatableLocaleField(): void
    {
        $this->expectException(PropertyDoesNotExistException::class);
        $this->expectExceptionMessage(
            'Neither class "stdClass" nor any of it\'s parent have the property "locale".'
        );

        new TranslatableConfiguration(
            stdClass::class,
            'locale',
            false,
            stdClass::class,
            'locale',
            'translatable',
            []
        );
    }

    public function testMismatchedNullableFieldTypeDeclaration(): void
    {
        $this->expectException(MismatchedNullableFieldTypeDeclarationException::class);
        $this->expectExceptionMessage(
            'Both translatable class "Tests\FSi\App\Entity\InvalidEntity" and'
            . ' translation class "Tests\FSi\App\Entity\InvalidEntity" should'
            . ' either allow or disallow NULL in property "nonNullableField".'
        );

        new TranslatableConfiguration(
            InvalidEntity::class,
            'locale',
            false,
            InvalidEntityTranslation::class,
            'locale',
            'entity',
            ['nonNullableField']
        );
    }

    public function testMismatchedFieldTypeDeclaration(): void
    {
        $this->expectException(MismatchedFieldTypeDeclarationException::class);
        $this->expectExceptionMessage(
            'Translatable class "Tests\FSi\App\Entity\InvalidEntity" and translation'
            . ' class "Tests\FSi\App\Entity\InvalidEntity" should have the same'
            . ' property type declaration for property "mismatchedField".'
        );

        new TranslatableConfiguration(
            InvalidEntity::class,
            'locale',
            false,
            InvalidEntityTranslation::class,
            'locale',
            'entity',
            ['mismatchedField']
        );
    }

    public function testMismatchedUndefinedFieldTypeDeclaration(): void
    {
        $this->expectException(MismatchedUndefinedFieldTypeDeclarationException::class);
        $this->expectExceptionMessage(
            'Translatable class "Tests\FSi\App\Entity\InvalidEntity" and translation'
            . ' class "Tests\FSi\App\Entity\InvalidEntity" should either both'
            . ' or none have field type declaration for property "undefinedFieldTypeDeclaration".'
        );

        new TranslatableConfiguration(
            InvalidEntity::class,
            'locale',
            false,
            InvalidEntityTranslation::class,
            'locale',
            'entity',
            ['undefinedFieldTypeDeclaration']
        );
    }
}
