<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\App\Http;

use FSi\Component\Translatable;

final class LocaleProvider implements Translatable\LocaleProvider
{
    private const DEFAULT_LOCALE = 'en';

    private static ?string $locale = null;

    public function getLocale(): string
    {
        return self::$locale ?? self::DEFAULT_LOCALE;
    }

    public function setLocale(string $locale): void
    {
        self::$locale = $locale;
    }

    public function clearSavedLocale(): void
    {
        self::$locale = null;
    }
}
