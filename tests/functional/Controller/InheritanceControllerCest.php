<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\Component\Translatable\Controller;

use Tests\FSi\App\Entity\HomePage;
use Tests\FSi\App\Entity\HomePageTranslation;
use Tests\FSi\FunctionalTester;

final class InheritanceControllerCest
{
    public function testPersistingInheritedFields(FunctionalTester $I): void
    {
        $I->amOnPage('/inheritance');

        $I->submitForm('[name="home_page"]', [
            'home_page' => [
                'title' => 'Title',
                'description' => 'Description',
                'preface' => 'Preface'
            ]
        ], 'Submit');

        $I->seeCurrentUrlEquals('/inheritance/1');
        $I->seeResponseCodeIs(200);

        /** @var HomePage $entity */
        $entity = $I->grabEntityFromRepository(HomePage::class, [
            'id' => 1
        ]);

        $I->seeInRepository(HomePageTranslation::class, [
            'locale' => 'en',
            'title' => 'Title',
            'description' => 'Description',
            'preface' => 'Preface',
            'page' => $entity
        ]);
    }
}
