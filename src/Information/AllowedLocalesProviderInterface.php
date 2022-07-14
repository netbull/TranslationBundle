<?php

namespace NetBull\TranslationBundle\Information;

interface AllowedLocalesProviderInterface
{
    /**
     * Return a list of the allowed locales
     * @return array
     */
    public function getAllowedLocales(): array;

    /**
     * Set the list of the allowed locales
     * @param array $allowedLocales
     */
    public function setAllowedLocales(array $allowedLocales);
}
