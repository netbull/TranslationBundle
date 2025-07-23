<?php

namespace NetBull\TranslationBundle\Information;

class AllowedLocalesProvider implements AllowedLocalesProviderInterface
{
    /**
     * @param array $allowedLocales
     */
    public function __construct(protected array $allowedLocales = [])
    {
    }

    /**
     * Return a list of the allowed locales
     */
    public function getAllowedLocales(): array
    {
        return $this->allowedLocales;
    }

    /**
     * Set the list of the allowed locales
     * @param array $allowedLocales
     */
    public function setAllowedLocales(array $allowedLocales): void
    {
        $this->allowedLocales = $allowedLocales;
    }
}
