<?php

namespace NetBull\TranslationBundle\Guessers;

/**
 * Class AbstractLocaleGuesser
 * @package NetBull\TranslationBundle\Guessers
 */
abstract class AbstractLocaleGuesser implements LocaleGuesserInterface
{
    /**
     * @var string
     */
    protected $identifiedLocale;

    /**
     * @return bool|string
     */
    public function getIdentifiedLocale()
    {
        if (null === $this->identifiedLocale) {
            return false;
        }

        return $this->identifiedLocale;
    }
}
