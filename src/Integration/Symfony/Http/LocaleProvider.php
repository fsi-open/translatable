<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable\Integration\Symfony\Http;

use Assert\Assertion;
use FSi\Component\Translatable;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class LocaleProvider implements Translatable\LocaleProvider
{
    private const SESSION_KEY = 'fsi_translatable.locale';

    private RequestStack $requestStack;
    private string $defaultLocale;

    public function __construct(RequestStack $requestStack, string $defaultLocale)
    {
        $this->requestStack = $requestStack;
        $this->defaultLocale = $defaultLocale;
    }

    public function getLocale(): string
    {
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

    private function getSavedLocale(): ?string
    {
        $session = $this->getSession();
        if (null === $session) {
            return null;
        }

        return $session->get(self::SESSION_KEY);
    }

    public function setLocale(string $locale): void
    {
        $session = $this->getSession();
        Assertion::notNull($session, "There is no session in which to save the locale in!");

        $session->set(self::SESSION_KEY, $locale);
    }

    public function clearSavedLocale(): void
    {
        $session = $this->getSession();
        Assertion::notNull($session, "There is no session to clear locale from!");

        $session->remove(self::SESSION_KEY);
    }

    private function getSession(): ?SessionInterface
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (null === $currentRequest) {
            return null;
        }

        return $currentRequest->getSession();
    }
}
