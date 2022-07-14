<?php

namespace NetBull\TranslationBundle\Information;

class AllowedLocalesProvider implements AllowedLocalesProviderInterface
{
    /**
     * @var array|null
     */
    protected $allowedLocales;

    /**
     * @param array $allowedLocales
     */
    public function __construct(array $allowedLocales = [])
    {
        $this->allowedLocales = $allowedLocales;
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
    public function setAllowedLocales(array $allowedLocales)
    {
        $this->allowedLocales = $allowedLocales;
    }
}
