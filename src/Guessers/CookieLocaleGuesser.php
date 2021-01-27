<?php

namespace NetBull\TranslationBundle\Guessers;

use Symfony\Component\HttpFoundation\Request;
use NetBull\TranslationBundle\Validator\MetaValidator;

/**
 * Class CookieLocaleGuesser
 * @package NetBull\TranslationBundle\Guessers
 */
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
     * CookieLocaleGuesser constructor.
     * @param MetaValidator $metaValidator
     * @param string $cookieName
     */
    public function __construct(MetaValidator $metaValidator, string $cookieName)
    {
        $this->metaValidator = $metaValidator;
        $this->cookieName = $cookieName;
    }

    /**
     * @inheritDoc
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
