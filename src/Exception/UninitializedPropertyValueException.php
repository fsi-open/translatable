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

final class UninitializedPropertyValueException extends TranslatableException
{
    public static function create(string $entityClass, string $propertyName): self
    {
        return new self(sprintf(
            'Property "%s" of class "%s" does not have a default value and does not permit null.',
            $propertyName,
            $entityClass
        ));
    }
}
