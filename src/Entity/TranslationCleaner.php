<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable\Entity;

use FSi\Component\Translatable\TranslationManager;

final class TranslationCleaner
{
    private TranslationManager $manager;

    public function __construct(TranslationManager $manager)
    {
        $this->manager = $manager;
    }

    public function clean(object $entity): void
    {
        $this->manager->clearTranslationsForEntity($entity);
    }
}
