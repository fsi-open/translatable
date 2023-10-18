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

final class MismatchedNullableFieldTypeDeclarationException extends TranslatableException
{
    public static function create(
        string $translatableClass,
        string $translationClass,
        string $propertyName
    ): self {
        return new self(sprintf(
            'Both translatable class "%s" and translation class "%s" should either'
            . ' allow or disallow NULL in property "%s".',
            $translatableClass,
            $translationClass,
            $propertyName
        ));
    }
}
