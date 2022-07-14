<?php

namespace NetBull\TranslationBundle\Guessers;

use Symfony\Component\HttpFoundation\Request;
use NetBull\TranslationBundle\Locale\LocaleMap;
use NetBull\TranslationBundle\Validator\MetaValidator;

class DomainLocaleGuesser extends AbstractLocaleGuesser
{
    /**
     * @var MetaValidator
     */
    private $metaValidator;

    /**
     * @var array
     */
    private $localeMap;

    /**
     * @param MetaValidator $metaValidator
     * @param LocaleMap $localeMap
     */
    public function __construct(MetaValidator $metaValidator, LocaleMap $localeMap)
    {
        $this->metaValidator = $metaValidator;
        $this->localeMap = $localeMap;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function guessLocale(Request $request): bool
    {
        $topLevelDomain = substr(strrchr($request->getHost(), '.'), 1);

        // use topLevelDomain as locale
        $locale = $topLevelDomain;

        // see if we have some additional mappings
        if ($topLevelDomain && $this->localeMap->getLocale($topLevelDomain)) {
            $locale = $this->localeMap->getLocale($topLevelDomain);
        }

        // now validate
        if (false !== $locale && $this->metaValidator->isAllowed($locale)) {
            $this->identifiedLocale = $locale;
            return true;
        }

        return false;
    }
}
