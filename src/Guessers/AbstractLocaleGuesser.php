<?php

namespace NetBull\TranslationBundle\Guessers;

abstract class AbstractLocaleGuesser implements LocaleGuesserInterface
{
    /**
     * @var string
     */
    protected $identifiedLocale;

    /**
     * @return string|null
     */
    public function getIdentifiedLocale(): ?string
    {
        if (null === $this->identifiedLocale) {
            return null;
        }

        return $this->identifiedLocale;
    }
}
