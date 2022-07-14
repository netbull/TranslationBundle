<?php

namespace NetBull\TranslationBundle\Guessers;

use Symfony\Component\HttpFoundation\Request;
use NetBull\TranslationBundle\Validator\MetaValidator;

class CookieLocaleGuesser extends AbstractLocaleGuesser
{
    /**
     * @var MetaValidator
     */
    private $metaValidator;

    /**
     * @var string
     */
    private $cookieName;

    /**
     * @param MetaValidator $metaValidator
     * @param string $cookieName
     */
    public function __construct(MetaValidator $metaValidator, string $cookieName)
    {
        $this->metaValidator = $metaValidator;
        $this->cookieName = $cookieName;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function guessLocale(Request $request): bool
    {
        if ($request->cookies->has($this->cookieName) && $this->metaValidator->isAllowed($request->cookies->get($this->cookieName))) {
            $this->identifiedLocale = $request->cookies->get($this->cookieName);
            return true;
        }

        return false;
    }
}
