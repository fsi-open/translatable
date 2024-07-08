<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable\Integration\Doctrine\ORM;

use Doctrine\Persistence\ManagerRegistry;
use FSi\Component\Translatable;
use RuntimeException;

use function get_class;

final class ClassProvider implements Translatable\ClassProvider
{
    private ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function forObject(object $block): string
    {
        $class = get_class($block);
        $manager = $this->registry->getManagerForClass($class);
        if (null === $manager) {
            return $class;
        }

        return $manager->getClassMetadata($class)->getName();
    }
}
