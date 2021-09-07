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
use FSi\Component\Translatable\ConfigurationResolver;
use FSi\Component\Translatable\Entity\TranslationLoader;
use FSi\Component\Translatable\Integration\Doctrine\ORM\ClassProvider;
use FSi\Component\Translatable\TranslatableConfiguration;
use FSi\Component\Translatable\TranslationManager;
use FSi\Component\Translatable\TranslationProvider;
use InvalidArgumentException;
use stdClass;
use Tests\FSi\App\Entity\Article;
use Tests\FSi\App\Entity\ArticleTranslation;
use Tests\FSi\App\Entity\Author;

final class TranslationLoaderTest extends Unit
{
    private ConfigurationResolver $entityConfigurationResolver;

    public function testTranslationDoesNotExist(): void
    {
        /** @var TranslationProvider $translationsProvider */
        $translationsProvider = $this->makeEmpty(TranslationProvider::class, [
            'findForEntityAndLocale' => Expected::once(null)
        ]);

        /** @var TranslationManager $translationManager */
        $translationManager = $this->makeEmpty(TranslationManager::class, [
            'sanitizeTranslationValue' => Expected::never()
        ]);

        $translatable = new Article(null, null);
        $loader = new TranslationLoader(
            $this->entityConfigurationResolver,
            $translationsProvider,
            $translationManager,
            new ClassProvider()
        );

        $loader->loadFromLocale($translatable, 'en');

        self::assertSame('en', $translatable->getLocale());
        self::assertSame(null, $translatable->getTitle());
        self::assertSame(null, $translatable->getDescription());
        self::assertSame(null, $translatable->getAuthor());
    }

    public function testTranslationExists(): void
    {
        $author = new Author('John Carpenter', 'Description');
        $translatable = new Article(null, null);
        $translation = new ArticleTranslation('en', 'Article', 'Description', $author, $translatable);

        /** @var TranslationProvider $translationsProvider */
        $translationsProvider = $this->makeEmpty(TranslationProvider::class, [
            'findForEntityAndLocale' => Expected::once($translation)
        ]);

        /** @var TranslationManager $translationManager */
        $translationManager = $this->makeEmpty(TranslationManager::class, [
            'sanitizeTranslationValue' => Expected::exactly(3, static fn($value) => $value)
        ]);

        $loader = new TranslationLoader(
            $this->entityConfigurationResolver,
            $translationsProvider,
            $translationManager,
            new ClassProvider()
        );
        $loader->loadFromLocale($translatable, 'en');

        self::assertSame('en', $translatable->getLocale());
        self::assertSame('Article', $translatable->getTitle());
        self::assertSame('Description', $translatable->getDescription());
        // Expected returns a different instance of the object passed as a return value,
        // so self::assertSame will not pass.
        self::assertEquals($author, $translatable->getAuthor());
    }

    public function testLoadingFromTranslation(): void
    {
        $author = new Author('John Carpenter', 'Description');
        $translatable = new Article(null, null);

        /** @var TranslationProvider $translationsProvider */
        $translationsProvider = $this->makeEmpty(TranslationProvider::class, [
            'findForEntityAndLocale' => Expected::never()
        ]);

        /** @var TranslationManager $translationManager */
        $translationManager = $this->makeEmpty(TranslationManager::class, [
            'sanitizeTranslationValue' => Expected::exactly(3, static fn($value) => $value)
        ]);

        $loader = new TranslationLoader(
            $this->entityConfigurationResolver,
            $translationsProvider,
            $translationManager,
            new ClassProvider()
        );

        $loader->loadFromTranslation(
            $translatable,
            new ArticleTranslation('en', 'Article', 'Description', $author, $translatable)
        );

        self::assertSame('en', $translatable->getLocale());
        self::assertSame('Article', $translatable->getTitle());
        self::assertSame('Description', $translatable->getDescription());
        // Expected returns a different instance of the object passed as a return value,
        // so self::assertSame will not pass.
        self::assertEquals($author, $translatable->getAuthor());
    }

    public function testLoadingFromTranslationIncorrectEntity(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Value "stdClass" is not the same as expected value "Tests\FSi\App\Entity\ArticleTranslation".');

        /** @var TranslationProvider $translationsProvider */
        $translationsProvider = $this->makeEmpty(TranslationProvider::class);

        /** @var TranslationManager $translationManager */
        $translationManager = $this->makeEmpty(TranslationManager::class);

        $loader = new TranslationLoader(
            $this->entityConfigurationResolver,
            $translationsProvider,
            $translationManager,
            new ClassProvider()
        );

        $loader->loadFromTranslation(new Article(null, null), new stdClass());
    }

    public function testLoadingFromTranslationForDifferentEntity(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            'Object of class "Tests\FSi\App\Entity\Article" has a different '
            . 'relation object for class "Tests\FSi\App\Entity\Article"'
        );

        /** @var TranslationProvider $translationsProvider */
        $translationsProvider = $this->makeEmpty(TranslationProvider::class);

        /** @var TranslationManager $translationManager */
        $translationManager = $this->makeEmpty(TranslationManager::class);

        $loader = new TranslationLoader(
            $this->entityConfigurationResolver,
            $translationsProvider,
            $translationManager,
            new ClassProvider()
        );

        $loader->loadFromTranslation(
            new Article(null, null),
            new ArticleTranslation('en', 'Article', 'Description', null, new Article())
        );
    }

    public function testLoadingFromTranslationWithoutALocale(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(
            'No locale for entity "Tests\FSi\App\Entity\ArticleTranslation"'
        );

        /** @var TranslationProvider $translationsProvider */
        $translationsProvider = $this->makeEmpty(TranslationProvider::class);

        /** @var TranslationManager $translationManager */
        $translationManager = $this->makeEmpty(TranslationManager::class);

        $loader = new TranslationLoader(
            $this->entityConfigurationResolver,
            $translationsProvider,
            $translationManager,
            new ClassProvider()
        );

        $entity = new Article(null, null);
        $loader->loadFromTranslation(
            $entity,
            new ArticleTranslation(null, 'Article', 'Description', null, $entity)
        );
    }

    /**
     * @phpcs:disable
     */
    protected function _before(): void
    {
        $this->entityConfigurationResolver = new ConfigurationResolver(
            new ClassProvider(),
            [
                new TranslatableConfiguration(
                    Article::class,
                    'locale',
                    ArticleTranslation::class,
                    'locale',
                    'article',
                    ['title', 'description', 'author']
                )
            ]
        );
    }
}
