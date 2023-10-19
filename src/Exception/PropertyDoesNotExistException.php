<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable\Exception;

final class PropertyDoesNotExistException extends TranslatableException
{
    public static function create(string $entityClass, string $propertyName): self
    {
        return new self(
            "Neither class \"{$entityClass}\" nor any of it's parent have the property \"{$propertyName}\"."
        );
    }
}
