<?php

namespace NetBull\TranslationBundle\Locale\Session;

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LocaleSession
{
    /**
     * @var RequestStack
     */
    private RequestStack $requestStack;
    /**
     * @var string
     */
    private string $sessionVar;

    /**
     * @param RequestStack $requestStack
     * @param string $sessionVar
     */
    public function __construct(RequestStack $requestStack, string $sessionVar = 'ntl')
    {
        $this->requestStack = $requestStack;
        $this->sessionVar = $sessionVar;
    }

    /**
     * @param string $locale
     * @return bool
     */
    public function hasLocaleChanged(string $locale): bool
    {
        if ($session = $this->getSession()) {
            return $locale !== $session->get($this->sessionVar);
        }

        return false;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale)
    {
        if ($session = $this->getSession()) {
            $session->set($this->sessionVar, $locale);
        }
    }

    /**
     * @param $locale
     * @return string|null
     */
    public function getLocale($locale): ?string
    {
        if ($session = $this->getSession()) {
            return $session->get($this->sessionVar, $locale);
        }

        return null;
    }

    /**
     * Returns the session var/key where the locale is saved in
     * @return string
     */
    public function getSessionVar(): string
    {
        return $this->sessionVar;
    }

    /**
     * @return SessionInterface|null
     */
    private function getSession(): ?SessionInterface
    {
        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException $e) {
            return null;
        }

        return $session;
    }
}
