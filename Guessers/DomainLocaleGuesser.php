<?php

namespace NetBull\TranslationBundle\Guessers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use NetBull\TranslationBundle\Locale\LocaleMap;
use NetBull\TranslationBundle\Validator\MetaValidator;

/**
 * Class DomainLocaleGuesser
 * @package NetBull\TranslationBundle\Guessers
 */
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
     * DomainLocaleGuesser constructor.
     * @param MetaValidator $metaValidator
     * @param LocaleMap $localeMap
     */
    public function __construct(MetaValidator $metaValidator, LocaleMap $localeMap)
    {
        $this->metaValidator = $metaValidator;
        $this->localeMap = $localeMap;
    }

    /**
     * Loops through all the activated Locale Guessers and
     * calls the guessLocale method and passing the current request.
     *
     * @param Request $request
     *
     * @throws InvalidConfigurationException
     *
     * @return boolean false if no locale is identified
     * @return bool the locale identified by the guessers
     */
    public function runLocaleGuessing(Request $request)
    {
        if (false !== $this->guessLocale($request)) {
            $locale = $this->getIdentifiedLocale();

            return $locale;
        }

        return false;
    }

    /**
     * Guess the locale based on the topLevelDomain
     *
     * @param Request $request
     * @return bool
     */
    public function guessLocale(Request $request)
    {
        $topLevelDomain = substr(strrchr($request->getHost(), '.'), 1);

        // Use topLevelDomain as locale
        $locale = $topLevelDomain;
        //see if we have some additional mappings
        if ($topLevelDomain && $this->localeMap->getLocale($topLevelDomain)) {
            $locale = $this->localeMap->getLocale($topLevelDomain);
        }

        //now validate
        if (false !== $locale && $this->metaValidator->isAllowed($locale)) {
            $this->identifiedLocale = $locale;
            return true;
        }

        return false;
    }
}
