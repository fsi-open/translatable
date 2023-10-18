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
use FSi\Component\Translatable\Exception\PropertyDoesNotExistException;
use FSi\Component\Translatable\TranslatableConfiguration;
use stdClass;

final class TranslatableConfigurationTest extends Unit
{
    public function testUsingNonExistentTranslatableClass(): void
    {
        $this->expectException(ClassDoesNotExistException::class);
        $this->expectExceptionMessage(
            'Translatable class "Some\Random\ClassName" does not exist.'
        );

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
}
