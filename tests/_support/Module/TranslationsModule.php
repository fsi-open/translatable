<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\Module;

use Codeception\Module;
use Codeception\Module\Symfony;
use Codeception\TestInterface;
use FSi\Component\Translatable\Integration\Symfony\Http\LocaleProvider;

final class TranslationsModule extends Module
{
    private Symfony $symfony;

    /**
     * @phpcs:disable
     * @param TestInterface $test
     * @return void
     */
    public function _before(TestInterface $test): void
    {
        /** @var Symfony $symfony */
        $symfony = $this->getModule('Symfony');
        $this->symfony = $symfony;
    }

    /**
     * @phpcs:disable
     * @param TestInterface $test
     * @return void
     */
    public function _failed(TestInterface $test, $fail): void
    {
        $this->clearSavedLocale();
    }

    public function enableLocaleProvider(): void
    {
        $this->getLocaleProvider()->enable();
    }

    public function disableLocaleProvider(): void
    {
        $this->getLocaleProvider()->disable();
    }

    public function setLocale(string $locale): void
    {
        $this->getLocaleProvider()->setLocale($locale);
    }

    public function clearSavedLocale(): void
    {
        $this->getLocaleProvider()->clearSavedLocale();
    }

    private function getLocaleProvider(): LocaleProvider
    {
        /** @var LocaleProvider $localeProvider */
        $localeProvider = $this->symfony->grabService(LocaleProvider::class);
        return $localeProvider;
    }
}
