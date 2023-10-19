<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable\Exception;

use function sprintf;

final class MissingTranslationProperty extends TranslatableException
{
    public static function create(string $translationClass, string $propertyName): self
    {
        return new self(sprintf(
            'Translation class "%s" should have the property "%s", but it does not.',
            $translationClass,
            $propertyName
        ));
    }
}
