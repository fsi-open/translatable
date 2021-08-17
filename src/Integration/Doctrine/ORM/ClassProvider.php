<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable\Integration\Doctrine\ORM;

use Doctrine\Common\Util\ClassUtils;
use FSi\Component;

use function get_class;

final class ClassProvider implements Component\Translatable\ClassProvider
{
    public function forObject(object $object): string
    {
        return ClassUtils::getRealClass(get_class($object));
    }
}
