<?php

namespace NetBull\TranslationBundle\Guessers;

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use NetBull\TranslationBundle\Validator\MetaValidator;

class SessionLocaleGuesser extends AbstractLocaleGuesser
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var MetaValidator
     */
    private $metaValidator;

    /**
     * @var string
     */
    private $sessionVariable;

    /**
     * @param RequestStack $requestStack
     * @param MetaValidator $metaValidator
     * @param string $sessionVariable
     */
    public function __construct(RequestStack $requestStack, MetaValidator $metaValidator, string $sessionVariable)
    {
        $this->requestStack = $requestStack;
        $this->metaValidator = $metaValidator;
        $this->sessionVariable = $sessionVariable;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function guessLocale(Request $request): bool
    {
        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException $e) {
            return false;
        }

        if ($session->has($this->sessionVariable)) {
            $locale = $session->get($this->sessionVariable);
            if (!$this->metaValidator->isAllowed($locale)) {
                return false;
            }
            $this->identifiedLocale = $session->get($this->sessionVariable);
            return true;
        }

        return false;
    }

    /**
     * Sets the locale in the session
     * @param string $locale Locale
     * @param bool $force Force write session
     */
    public function setSessionLocale(string $locale, bool $force = false)
    {
        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException $e) {
            return false;
        }

        if (!$session->has($this->sessionVariable) || $force) {
            $session->set($this->sessionVariable, $locale);
        }
    }
}
