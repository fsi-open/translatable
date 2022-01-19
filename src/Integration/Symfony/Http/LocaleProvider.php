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
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class LocaleProvider implements Translatable\LocaleProvider
{
    private const LOCALE_SESSION_KEY = 'fsi_translatable.locale';
    private const DISABLE_SESSION_KEY = 'fsi_translatable.disabled';

    private RequestStack $requestStack;
    private SessionInterface $session;
    private string $defaultLocale;

    public function __construct(
        RequestStack $requestStack,
        SessionInterface $session,
        string $defaultLocale
    ) {
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->defaultLocale = $defaultLocale;
    }

    public function getLocale(): ?string
    {
        if (true === $this->isDisabled()) {
            return null;
        }

        $savedLocale = $this->getSavedLocale();
        if (null !== $savedLocale) {
            return $savedLocale;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request) {
            return $request->getLocale();
        }

        return $this->defaultLocale;
    }

    public function enable(): void
    {
        $this->getSession()->set(self::DISABLE_SESSION_KEY, false);
    }

    public function disable(): void
    {
        $this->getSession()->set(self::DISABLE_SESSION_KEY, true);
    }

    private function getSavedLocale(): ?string
    {
        return $this->getSession()->get(self::LOCALE_SESSION_KEY);
    }

    public function setLocale(string $locale): void
    {
        $this->getSession()->set(self::LOCALE_SESSION_KEY, $locale);
    }

    public function clearSavedLocale(): void
    {
        $this->getSession()->remove(self::LOCALE_SESSION_KEY);
    }

    private function isDisabled(): bool
    {
        return $this->getSession()->get(self::DISABLE_SESSION_KEY, false);
    }

    private function getSession(): SessionInterface
    {
//        TODO use after dropping Symfony 4.4
//        return $this->requestStack->getSession();
        return $this->session;
    }
}
