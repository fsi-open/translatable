<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\Component\Translatable\Controller;

use Tests\FSi\App\Entity\Post;
use Tests\FSi\App\Entity\PostTranslation;
use Tests\FSi\FunctionalTester;

final class DisabledAutoUpdateControllerCest
{
    public function testNoAutoUpdate(FunctionalTester $I): void
    {
        $I->amOnPage('/post');
        $I->seeResponseCodeIs(200);

        $I->submitForm('[name="post"]', [
            'post' => [
                'title' => 'Title',
                'content' => 'Content'
            ]
        ], 'Submit');

        $I->seeCurrentUrlEquals('/post/1');
        $I->seeResponseCodeIs(200);

        $I->seeInRepository(Post::class, [
            'id' => 1
        ]);

        $I->dontSeeInRepository(PostTranslation::class);

        $I->seeCurrentUrlEquals('/post/1');
        $I->seeResponseCodeIs(200);

        $I->submitForm('[name="post"]', [
            'post' => [
                'title' => 'Title',
                'content' => 'Content'
            ]
        ], 'Submit');

        $I->dontSeeInRepository(PostTranslation::class);

        /** @var Post $post */
        $post = $I->grabEntityFromRepository(Post::class, ['id' => 1]);
        $I->assertSame('en', $post->getLocale());
        $I->assertNull($post->getTitle());
        $I->assertNull($post->getContent());
    }
}
