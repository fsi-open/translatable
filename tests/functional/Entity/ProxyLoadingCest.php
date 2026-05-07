<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\Component\Translatable\Entity;

use DateTimeImmutable;
use Tests\FSi\App\Entity\Article;
use Tests\FSi\App\Entity\Post;
use Tests\FSi\FunctionalTester;

final class ProxyLoadingCest
{
    public function testProxyLoading(FunctionalTester $I): void
    {
        $articleId = $I->haveInRepository(Article::class, [
            'locale' => 'en',
            'publicationDate' => new DateTimeImmutable(),
            'title' => 'Title',
            'description' => 'Description',
        ]);
        $article = $I->grabEntityFromRepository(Article::class, ['id' => $articleId]);

        $postId = $I->haveInRepository(Post::class, [
            'locale' => 'en',
            'title' => 'Title',
            'content' => 'Content',
            'article' => $article,
        ]);

        $I->clearEntityManager();

        $post = $I->grabEntityFromRepository(Post::class, ['id' => $postId]);
        $I->assertInstanceOf(Article::class, $post->getArticle());
        $I->flushToDatabase();
    }
}
