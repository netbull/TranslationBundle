<?php

namespace NetBull\TranslationBundle\Locale\Session;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class LocaleSession
 * @package NetBull\TranslationBundle\Locale\Session
 */
class LocaleSession
{
    /**
     * @var Session
     */
    private $session;
    /**
     * @var string
     */
    private $sessionVar;

    /**
     * LocaleSession constructor.
     * @param Session $session
     * @param string $sessionVar
     */
    public function __construct(Session $session, $sessionVar = 'ntl')
    {
        $this->session = $session;
        $this->sessionVar = $sessionVar;
    }

    /**
     * Checks if the locale has changes
     *
     * @param string $locale
     * @return bool
     */
    public function hasLocaleChanged($locale)
    {
        return $locale !== $this->session->get($this->sessionVar);
    }

    /**
     * Sets the locale
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->session->set($this->sessionVar, $locale);
    }

    /**
     * Returns the locale
     *
     * @param $locale
     * @return string
     */
    public function getLocale($locale)
    {
        return $this->session->get($this->sessionVar, $locale);
    }

    /**
     * Returns the session var/key where the locale is saved in
     *
     * @return string
     */
    public function getSessionVar()
    {
        return $this->sessionVar;
    }
}
