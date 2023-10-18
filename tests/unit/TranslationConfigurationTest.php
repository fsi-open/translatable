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
use FSi\Component\Translatable\TranslationConfiguration;
use stdClass;

final class TranslationConfigurationTest extends Unit
{
    public function testNonExistentTranslationClass(): void
    {
        $this->expectException(ClassDoesNotExistException::class);
        $this->expectExceptionMessage('Translation class "Some\Random\ClassName" does not exist.');

        /** @var class-string<object> $nonExistentClassString */
        $nonExistentClassString = 'Some\Random\ClassName';
        new TranslationConfiguration(
            $nonExistentClassString,
            'locale',
            'translatable',
            []
        );
    }

    public function testNonExistentTranslationLocaleField(): void
    {
        $this->expectException(PropertyDoesNotExistException::class);
        $this->expectExceptionMessage(
            'Neither class "stdClass" nor any of it\'s parent have the property "locale".'
        );

        new TranslationConfiguration(
            stdClass::class,
            'locale',
            'translatable',
            []
        );
    }
}
