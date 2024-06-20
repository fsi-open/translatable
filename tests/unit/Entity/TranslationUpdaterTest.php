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
use FSi\Component\Translatable\ClassProvider;
use FSi\Component\Translatable\ConfigurationResolver;
use FSi\Component\Translatable\Entity\TranslationUpdater;
use FSi\Component\Translatable\TranslatableConfiguration;
use FSi\Component\Translatable\TranslationManager;
use FSi\Component\Translatable\TranslationProvider;
use Tests\FSi\App\Entity\Article;
use Tests\FSi\App\Entity\ArticleTranslation;
use Tests\FSi\App\Entity\Author;

use function get_class;

final class TranslationUpdaterTest extends Unit
{
    private ConfigurationResolver $entityConfigurationResolver;

    public function testCreatingNewTranslation(): void
    {
        $translatable = new Article('Article', 'Description');
        $translatable->setLocale('en');
        $translation = new ArticleTranslation('en', null, null, null, $translatable);

        /** @var TranslationProvider $translationsProvider */
        $translationsProvider = $this->makeEmpty(TranslationProvider::class, [
            'createForEntityAndLocale' => Expected::once($translation),
            'findForEntityAndLocale' => Expected::once(null)
        ]);

        /** @var TranslationManager $translationManager */
        $translationManager = $this->makeEmpty(TranslationManager::class, [
            'removeTranslation' => Expected::never(),
            'isTranslationEmpty' => Expected::once(false),
            'saveTranslation' => Expected::once()
        ]);

        $loader = new TranslationUpdater(
            $this->entityConfigurationResolver,
            $translationsProvider,
            $translationManager
        );

        $loader->update($translatable);
    }

    public function testUpdatingExistingTranslation(): void
    {
        $author = new Author('John Carpenter', 'Description');
        $translatable = new Article('Article updated', 'Description updated');
        $translatable->setLocale('en');
        $translatable->setAuthor($author);

        $translation = new ArticleTranslation('en', 'Article', 'Description', null, $translatable);

        /** @var TranslationProvider $translationsProvider */
        $translationsProvider = $this->makeEmpty(TranslationProvider::class, [
            'createForEntityAndLocale' => Expected::never(),
            'findForEntityAndLocale' => Expected::once($translation)
        ]);

        /** @var TranslationManager $translationManager */
        $translationManager = $this->makeEmpty(TranslationManager::class, [
            'removeTranslation' => Expected::never(),
            'isTranslationEmpty' => Expected::once(false),
            'saveTranslation' => Expected::never(),
            'sanitizeTranslatableValue' => Expected::exactly(
                3,
                static fn(
                    object $translation,
                    string $field,
                    $translatableValue
                ) => $translatableValue
            )
        ]);

        $loader = new TranslationUpdater(
            $this->entityConfigurationResolver,
            $translationsProvider,
            $translationManager
        );

        $loader->update($translatable);

        self::assertSame('en', $translatable->getLocale());
        self::assertSame('Article updated', $translation->getTitle());
        self::assertSame('Description updated', $translation->getDescription());
        self::assertEquals($author, $translation->getAuthor());
        self::assertEquals($translatable, $translation->getArticle());
    }

    public function testTranslationRemoval(): void
    {
        $translatable = new Article(null, null);
        $translatable->setLocale('en');
        $translatable->setAuthor(null);

        $author = new Author('John Carpenter', 'Description');
        $translation = new ArticleTranslation('en', 'Article', 'Description', $author, $translatable);

        /** @var TranslationProvider $translationsProvider */
        $translationsProvider = $this->makeEmpty(TranslationProvider::class, [
            'createForEntityAndLocale' => Expected::never(),
            'findForEntityAndLocale' => Expected::once($translation)
        ]);

        /** @var TranslationManager $translationManager */
        $translationManager = $this->makeEmpty(TranslationManager::class, [
            'isTranslationEmpty' => Expected::once(true),
            'removeTranslation' => Expected::once(),
            'saveTranslation' => Expected::never()
        ]);

        $loader = new TranslationUpdater(
            $this->entityConfigurationResolver,
            $translationsProvider,
            $translationManager
        );

        $loader->update($translatable);

        self::assertSame('en', $translatable->getLocale());
        self::assertSame(null, $translation->getTitle());
        self::assertSame(null, $translation->getDescription());
        self::assertSame(null, $translation->getAuthor());
        self::assertSame($translatable, $translation->getArticle());
    }

    public function testNewTranslationWithADifferentLocale(): void
    {
        $author = new Author('John Carpenter', 'Description');
        $translatable = new Article('Article updated', 'Description updated');
        $translatable->setLocale('en');
        $translatable->setAuthor($author);

        $translationEn = new ArticleTranslation('en', 'Article', 'Description', null, $translatable);
        $translationPl = new ArticleTranslation('pl', null, null, null, $translatable);

        /** @var TranslationProvider $translationsProvider */
        $translationsProvider = $this->makeEmpty(TranslationProvider::class, [
            'findForEntityAndLocale' => Expected::exactly(
                2,
                static fn(object $entity, string $locale): ?object
                    => 'en' === $locale ? $translationEn : null
            ),
            'createForEntityAndLocale' => Expected::once($translationPl)
        ]);

        /** @var TranslationManager $translationManager */
        $translationManager = $this->makeEmpty(TranslationManager::class, [
            'removeTranslation' => Expected::never(),
            'isTranslationEmpty' => Expected::exactly(2, false),
            'saveTranslation' => Expected::once(),
            'sanitizeTranslatableValue' => Expected::exactly(
                6,
                static fn(
                    object $translation,
                    string $field,
                    $translatableValue
                ) => $translatableValue
            )
        ]);

        $loader = new TranslationUpdater(
            $this->entityConfigurationResolver,
            $translationsProvider,
            $translationManager
        );

        $loader->update($translatable);

        $translatable->setLocale('pl');
        $translatable->setTitle('Artykuł');
        $translatable->setDescription('Opis');

        $loader->update($translatable);

        self::assertSame('en', $translationEn->getLocale());
        self::assertSame('Article updated', $translationEn->getTitle());
        self::assertSame('Description updated', $translationEn->getDescription());
    }

    /**
     * @phpcs:disable
     */
    protected function _before(): void
    {
        $this->entityConfigurationResolver = new ConfigurationResolver(
            $this->createClassProvider(),
            [
                new TranslatableConfiguration(
                    Article::class,
                    'locale',
                    false,
                    ArticleTranslation::class,
                    'locale',
                    'article',
                    ['title', 'description', 'author']
                )
            ]
        );
    }

    private function createClassProvider(): ClassProvider
    {
        $classProvider = $this->createMock(ClassProvider::class);
        $classProvider->method('forObject')->willReturnCallback(function (object $object): string {
            return get_class($object);
        });

        return $classProvider;
    }
}
