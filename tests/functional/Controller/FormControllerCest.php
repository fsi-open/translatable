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
use FSi\Component\Files\WebFile;
use Tests\FSi\App\Entity\Article;
use Tests\FSi\App\Entity\ArticleTranslation;
use Tests\FSi\App\Entity\Banner;
use Tests\FSi\App\Entity\Comment;
use Tests\FSi\FunctionalTester;

final class FormControllerCest
{
    public function testFullSubmit(FunctionalTester $I): void
    {
        $I->amOnPage('/');

        $I->attachFile('Photo', 'test.jpg');
        $I->attachFile('[name="article[banner][image]"]', 'test.jpg');
        $I->submitForm('[name="article"]', [
            'article' => [
                'title' => 'Title',
                'description' => 'Description',
                'publicationDate' => '2021-07-01',
                'author' => [
                    'name' => 'Henry Hortinson',
                    'description' => 'Description',
                    'city' => [
                        'name' => 'New York'
                    ]
                ],
                'comments' => [
                    ['content' => 'Content']
                ]
            ]
        ], 'Submit');

        $publicationDate = new DateTimeImmutable('2021-07-01');

        $I->seeCurrentUrlEquals('/1');
        $I->seeResponseCodeIs(200);

        $I->seeInRepository(Article::class, [
            'publicationDate' => $publicationDate
        ]);

        /** @var Article $entity */
        $entity = $I->grabEntityFromRepository(Article::class, [
            'publicationDate' => $publicationDate
        ]);

        $I->seeInRepository(ArticleTranslation::class, [
            'locale' => 'en',
            'title' => 'Title',
            'description' => 'Description',
            'article' => $entity,
            'author.name' => 'Henry Hortinson',
            'author.description' => 'Description',
            'author.city.name' => 'New York'
        ]);

        /** @var ArticleTranslation $translationEn */
        $translationEn = $I->grabEntityFromRepository(ArticleTranslation::class, [
            'locale' => 'en',
            'article' => $entity
        ]);

        $I->assertCount(1, $translationEn->getComments());
        $I->assertInstanceOf(WebFile::class, $entity->getPhoto());
        $I->assertInstanceOf(WebFile::class, $translationEn->getPhoto());
        $I->assertInstanceOf(Banner::class, $entity->getBanner());
        $I->assertInstanceOf(Banner::class, $translationEn->getBanner());

        /** @var Banner $entityBanner */
        $entityBanner = $entity->getBanner();
        /** @var Banner $translationEnBanner */
        $translationEnBanner = $translationEn->getBanner();
        $I->assertInstanceOf(WebFile::class, $entityBanner->getImage());
        $I->assertInstanceOf(WebFile::class, $translationEnBanner->getImage());

        $I->amOnPage('/pl/1');
        $I->setLocale('pl');
        $I->submitForm('[name="article"]', [
            'article' => [
                'title' => 'Tytuł',
                'description' => 'Opis',
                'publicationDate' => '2021-07-02',
                'author' => [
                    'name' => 'Henryk Marowski',
                    'description' => 'Opis',
                    'city' => [
                        'name' => 'Kraków'
                    ]
                ],
                'comments' => [
                    ['content' => 'Komentarz 1'],
                    ['content' => 'Komentarz 2']
                ]
            ]
        ], 'Submit');

        $publicationDate = new DateTimeImmutable('2021-07-02');

        $I->seeCurrentUrlEquals('/pl/1');
        $I->seeResponseCodeIs(200);

        $I->seeInRepository(Article::class, [
            'publicationDate' => $publicationDate
        ]);

        /** @var Article $entity */
        $entity = $I->grabEntityFromRepository(Article::class, [
            'publicationDate' => $publicationDate
        ]);

        $I->seeInRepository(ArticleTranslation::class, [
            'locale' => 'en',
            'title' => 'Title',
            'description' => 'Description',
            'article' => $entity,
            'author.name' => 'Henry Hortinson',
            'author.description' => 'Description',
            'author.city.name' => 'New York'
        ]);

        $I->seeInRepository(ArticleTranslation::class, [
            'locale' => 'pl',
            'title' => 'Tytuł',
            'description' => 'Opis',
            'article' => $entity,
            'author.name' => 'Henryk Marowski',
            'author.description' => 'Opis',
            'author.city.name' => 'Kraków'
        ]);

        /** @var ArticleTranslation $translationPl */
        $translationPl = $I->grabEntityFromRepository(ArticleTranslation::class, [
            'locale' => 'pl'
        ]);

        $I->assertSame('pl', $entity->getLocale());
        $I->assertCount(1, $translationEn->getComments());
        $I->assertCount(2, $translationPl->getComments());
        $I->assertCount(2, $entity->getComments());

        $commentsPlIds = array_map(
            static fn(Comment $comment): ?int => $comment->getId(),
            $translationPl->getComments()
        );

        $I->submitForm('[name="article"]', [
            'article' => [
                'title' => 'Tytuł',
                'description' => 'Opis',
                'publicationDate' => '2021-07-02',
                'author' => [
                    'name' => 'Henryk Marowski',
                    'description' => 'Opis'
                ],
                'comments' => [
                    ['content' => 'Komentarz 1'],
                    ['content' => 'Komentarz 2']
                ]
            ]
        ], 'Submit');

        $I->seeInRepository(ArticleTranslation::class, ['locale' => 'pl']);


        /** @var ArticleTranslation $translationPl */
        $translationPl = $I->grabEntityFromRepository(ArticleTranslation::class, [
            'locale' => 'pl'
        ]);

        // Assert that the translatable collection is not replaced with a fresh
        // instance on each submit
        $I->assertCount(2, $translationPl->getComments());
        $I->assertContainsOnly('int', $commentsPlIds);
        $I->assertSame(
            $commentsPlIds,
            array_map(
                static fn(Comment $comment): ?int => $comment->getId(),
                $translationPl->getComments()
            )
        );

        $I->submitForm('[name="article"]', [
            'article' => [
                'title' => 'Tytuł',
                'description' => 'Opis',
                'publicationDate' => '2021-07-02',
                'author' => [
                    'name' => 'Henryk Marowski',
                    'description' => 'Opis'
                ],
                'comments' => [
                    ['content' => 'Komentarz 1'],
                    ['content' => 'Komentarz 2'],
                    ['content' => 'Komentarz 3']
                ]
            ]
        ], 'Submit');

        /** @var ArticleTranslation $translationPl */
        $translationPl = $I->grabEntityFromRepository(ArticleTranslation::class, [
            'locale' => 'pl'
        ]);

        $I->assertCount(3, $translationPl->getComments());
    }

    public function testLocaleDisabled(FunctionalTester $I): void
    {
        $I->amOnPage('/');

        $I->disableLocaleProvider();
        $I->submitForm('[name="article"]', [
            'article' => [
                'title' => '',
                'description' => '',
                'publicationDate' => '2021-07-01',
                'author' => [
                    'name' => 'Henry Hortinson',
                    'description' => 'Description',
                    'city' => ['name' => 'New York']
                ],
                'comments' => []
            ]
        ], 'Submit');

        $I->seeCurrentUrlEquals('/1');
        $I->seeResponseCodeIs(200);

        $I->dontSeeInRepository(ArticleTranslation::class);

        $I->enableLocaleProvider();
    }

    public function testOnlyEmbeddables(FunctionalTester $I): void
    {
        $I->amOnPage('/');
        $I->submitForm('[name="article"]', [
            'article' => [
                'title' => '',
                'description' => '',
                'publicationDate' => '2021-07-01',
                'author' => [
                    'name' => 'Henry Hortinson',
                    'description' => 'Description',
                    'city' => ['name' => 'New York']
                ],
                'comments' => []
            ]
        ], 'Submit');

        $publicationDate = new DateTimeImmutable('2021-07-01');

        $I->seeCurrentUrlEquals('/1');
        $I->seeResponseCodeIs(200);

        $I->seeInRepository(Article::class, [
            'publicationDate' => $publicationDate
        ]);

        /** @var Article $entity */
        $entity = $I->grabEntityFromRepository(Article::class, [
            'publicationDate' => $publicationDate
        ]);

        $I->seeInRepository(ArticleTranslation::class, [
            'locale' => 'en',
            'title' => null,
            'description' => null,
            'article' => $entity,
            'author.name' => 'Henry Hortinson',
            'author.description' => 'Description',
            'author.city.name' => 'New York'
        ]);

        $I->amOnPage('/1');
        $I->submitForm('[name="article"]', [
            'article' => [
                'title' => '',
                'description' => '',
                'publicationDate' => '2021-07-01',
                'author' => [
                    'name' => '',
                    'description' => '',
                    'city' => ['name' => 'New York']
                ],
                'comments' => []
            ]
        ], 'Submit');

        $I->seeCurrentUrlEquals('/1');
        $I->seeResponseCodeIs(200);

        /** @var Article $entity */
        $entity = $I->grabEntityFromRepository(Article::class, [
            'publicationDate' => $publicationDate
        ]);

        $I->seeInRepository(ArticleTranslation::class, [
            'locale' => 'en',
            'title' => null,
            'description' => null,
            'article' => $entity,
            'author.name' => null,
            'author.description' => null,
            'author.city.name' => 'New York'
        ]);

        $I->amOnPage('/1');
        $I->submitForm('[name="article"]', [
            'article' => [
                'title' => '',
                'description' => '',
                'publicationDate' => '2021-07-01',
                'author' => [
                    'name' => '',
                    'description' => '',
                    'city' => ['name' => '']
                ],
                'comments' => []
            ]
        ], 'Submit');

        $I->seeCurrentUrlEquals('/1');
        $I->seeResponseCodeIs(200);

        $I->dontSeeInRepository(ArticleTranslation::class);
    }

    public function testNoEmbeddableSubmitAndSingleTranslationRemoval(FunctionalTester $I): void
    {
        $I->amOnPage('/');
        $I->attachFile('Photo', 'test.jpg');
        $I->submitForm('[name="article"]', [
            'article' => [
                'title' => 'Title',
                'description' => 'Description',
                'publicationDate' => '2021-07-01',
                'author' => [
                    'name' => '',
                    'description' => '',
                    'city' => ['name' => '']
                ],
                'comments' => [['content' => 'Content']]
            ]
        ], 'Submit');

        $publicationDate = new DateTimeImmutable('2021-07-01');

        $I->seeCurrentUrlEquals('/1');
        $I->seeResponseCodeIs(200);

        $I->seeInRepository(Article::class, [
            'publicationDate' => $publicationDate
        ]);

        /** @var Article $entity */
        $entity = $I->grabEntityFromRepository(Article::class, [
            'publicationDate' => $publicationDate
        ]);

        $I->seeInRepository(ArticleTranslation::class, [
            'locale' => 'en',
            'title' => 'Title',
            'description' => 'Description',
            'article' => $entity,
            'author.name' => null,
            'author.description' => null,
            'author.city.name' => null
        ]);

        $I->setLocale('pl');
        $I->amOnPage('/pl/1');
        $I->submitForm('[name="article"]', [
            'article' => [
                'title' => 'Tytuł',
                'description' => 'Opis',
                'publicationDate' => '2021-07-02',
                'author' => [
                    'name' => 'Henryk Marowski',
                    'description' => 'Opis',
                    'city' => ['name' => 'Kraków']
                ],
                'comments' => [
                ]
            ]
        ], 'Submit');

        $I->seeCurrentUrlEquals('/pl/1');
        $I->seeResponseCodeIs(200);

        $I->seeInRepository(ArticleTranslation::class, ['locale' => 'en']);
        $I->seeInRepository(ArticleTranslation::class, ['locale' => 'pl']);

        $I->amOnPage('/pl/1');
        $I->submitForm('[name="article"]', [
            'article' => [
                'title' => '',
                'description' => '',
                'publicationDate' => '2021-07-02',
                'author' => [
                    'name' => '',
                    'description' => '',
                    'city' => ['name' => '']
                ],
                'comments' => []
            ]
        ], 'Submit');

        $I->seeCurrentUrlEquals('/pl/1');
        $I->seeResponseCodeIs(200);

        $I->seeInRepository(ArticleTranslation::class, ['locale' => 'en']);
        $I->dontSeeInRepository(ArticleTranslation::class, ['locale' => 'pl']);
    }

    public function testOnlyOneToOneTranslatable(FunctionalTester $I): void
    {
        $I->amOnPage('/');

        $I->attachFile('[name="article[banner][image]"]', 'test.jpg');
        $I->submitForm('[name="article"]', [
            'article' => [
                'title' => '',
                'description' => '',
                'publicationDate' => '2021-07-01',
                'author' => [
                    'name' => '',
                    'description' => '',
                    'city' => ['name' => '']
                ],
                'comments' => []
            ]
        ], 'Submit');

        $I->seeCurrentUrlEquals('/1');
        $I->seeResponseCodeIs(200);

        /** @var Article $entity */
        $entity = $I->grabEntityFromRepository(Article::class, [
            'publicationDate' => new DateTimeImmutable('2021-07-01')
        ]);

        $I->seeInRepository(ArticleTranslation::class, [
            'locale' => 'en',
            'title' => null,
            'description' => null,
            'article' => $entity,
            'author.name' => null,
            'author.description' => null,
            'author.city.name' => null
        ]);

        /** @var ArticleTranslation $translationEn */
        $translationEn = $I->grabEntityFromRepository(ArticleTranslation::class, [
            'locale' => 'en',
            'article' => $entity
        ]);

        $I->assertInstanceOf(Banner::class, $entity->getBanner());
        $I->assertInstanceOf(Banner::class, $translationEn->getBanner());
    }

    public function testNoTranslation(FunctionalTester $I): void
    {
        $I->amOnPage('/');
        $I->submitForm('[name="article"]', [
            'article' => [
                'title' => '',
                'description' => '',
                'publicationDate' => '2021-07-01',
                'author' => [
                    'name' => '',
                    'description' => '',
                    'city' => ['name' => '']
                ],
                'comments' => []
            ]
        ], 'Submit');

        $I->seeCurrentUrlEquals('/1');
        $I->seeResponseCodeIs(200);

        $I->seeInRepository(Article::class, [
            'publicationDate' => new DateTimeImmutable('2021-07-01')
        ]);

        $I->dontSeeInRepository(ArticleTranslation::class, [
            'locale' => 'en'
        ]);
    }

    /**
     * @phpcs:disable
     * @param FunctionalTester $I
     * @return void
     */
    public function _after(FunctionalTester $I): void
    {
        $I->clearSavedLocale();
    }
}
