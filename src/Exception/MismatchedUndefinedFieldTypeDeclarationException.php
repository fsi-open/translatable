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

final class MismatchedUndefinedFieldTypeDeclarationException extends TranslatableException
{
    public static function create(
        string $translatableClass,
        string $translationClass,
        string $propertyName
    ): self {
        return new self(sprintf(
            'Translatable class "%s" and translation class "%s" should either both'
            . ' or none have field type declaration for property "%s".',
            $translatableClass,
            $translationClass,
            $propertyName
        ));
    }
}
