<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\Component\Translatable\Controller;

use DateTimeImmutable;
use Tests\FSi\App\Entity\Article;
use Tests\FSi\App\Entity\ArticleTranslation;
use Tests\FSi\App\Entity\Author;
use Tests\FSi\App\Entity\Banner;
use Tests\FSi\App\Entity\Comment;
use Tests\FSi\FunctionalTester;

final class RemoveControllerCest
{
    public function testEntityRemoval(FunctionalTester $I): void
    {
        /** @var int $articleId */
        $articleId = $I->haveInRepository(Article::class, [
            'publicationDate' => new DateTimeImmutable()
        ]);

        $banner = new Banner();
        $banner->setImage($I->createTestWebFile());

        /** @var Article $article */
        $article = $I->grabEntityFromRepository(Article::class, ['id' => $articleId]);
        $article->setTitle('Title');
        $article->setDescription('Description');
        $article->setPhoto($I->createTestWebFile());
        $article->setAuthor(new Author('John Carpenter', 'A famous writer'));
        $article->setBanner($banner);
        $article->addComment(new Comment('Grand indeed'));
        $article->addComment(new Comment('Marvelous'));

        $I->seeInRepository(ArticleTranslation::class, ['locale' => 'en']);

        $article->setLocale('pl');
        $article->setTitle('Tytuł');
        $article->setDescription('Opis');
        $article->setPhoto($I->createTestWebFile());
        $article->setBanner(null);
        $article->setAuthor(new Author('Jan Jakubowicz', 'Słynny pisarz'));
        $article->addComment(new Comment('Niesamowite'));

        $I->seeInRepository(ArticleTranslation::class, ['locale' => 'pl']);

        $I->amOnPage("/remove/{$articleId}");
        $I->seeResponseCodeIs(200);
        $I->see('OK');

        $I->dontSeeInRepository(Article::class);
        $I->dontSeeInRepository(ArticleTranslation::class);
        $I->dontSeeInRepository(Banner::class);
    }
}
