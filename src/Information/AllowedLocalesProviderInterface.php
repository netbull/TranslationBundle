<?php

namespace NetBull\TranslationBundle\Information;

/**
 * Interface AllowedLocalesProviderInterface
 * @package NetBull\TranslationBundle\Information
 */
interface AllowedLocalesProviderInterface
{
    /**
     * Return a list of the allowed locales
     * @return array
     */
    public function getAllowedLocales();

    /**
     * Set the list of the allowed locales
     * @param array $allowedLocales
     */
    public function setAllowedLocales($allowedLocales);
}
