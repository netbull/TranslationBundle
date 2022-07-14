<?php

namespace NetBull\TranslationBundle\Guessers;

abstract class AbstractLocaleGuesser implements LocaleGuesserInterface
{
    /**
     * @var string|null
     */
    protected ?string $identifiedLocale = null;

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
