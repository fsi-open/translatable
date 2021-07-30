<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\Component\Translatable\Entity;

use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use FSi\Component\Translatable\Entity\TranslationCleaner;
use FSi\Component\Translatable\TranslationManager;
use Tests\FSi\App\Entity\Article;

final class TranslationCleanerTest extends Unit
{
    public function testRemoval(): void
    {
        $translatable = new Article();

        /** @var TranslationManager $translationsManager */
        $translationsManager = $this->makeEmpty(TranslationManager::class, [
            'clearTranslationsForEntity' => Expected::once(
                function ($object) use ($translatable): void {
                    $this->assertEquals($translatable, $object);
                }
            )
        ]);

        $cleaner = new TranslationCleaner($translationsManager);
        $cleaner->clean($translatable);
    }
}
