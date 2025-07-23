<?php

namespace NetBull\TranslationBundle\Guessers;

use Symfony\Component\HttpFoundation\Request;
use NetBull\TranslationBundle\Locale\LocaleMap;
use NetBull\TranslationBundle\Validator\MetaValidator;

class DomainLocaleGuesser extends AbstractLocaleGuesser
{
    /**
     * @param MetaValidator $metaValidator
     * @param LocaleMap $localeMap
     */
    public function __construct(
        private MetaValidator $metaValidator,
        private LocaleMap $localeMap
    ) {
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
