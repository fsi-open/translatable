<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable\Integration\Symfony\Http;

use FSi\Component\Translatable;
use Symfony\Component\HttpFoundation\RequestStack;

final class LocaleProvider implements Translatable\LocaleProvider
{
    private RequestStack $requestStack;
    private string $defaultLocale;
    private ?string $savedLocale;

    public function __construct(RequestStack $requestStack, string $defaultLocale)
    {
        $this->requestStack = $requestStack;
        $this->defaultLocale = $defaultLocale;
        $this->savedLocale = null;
    }

    public function getLocale(): string
    {
        if (null !== $this->savedLocale) {
            return $this->savedLocale;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request) {
            return $request->getLocale();
        }

        return $this->defaultLocale;
    }

    public function saveLocale(string $locale): void
    {
        $this->savedLocale = $locale;
    }

    public function resetSavedLocale(): void
    {
        $this->savedLocale = null;
    }
}
