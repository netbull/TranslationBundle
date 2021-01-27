<?php

namespace NetBull\TranslationBundle\Locale;

/**
 * Class CountryMap
 * @package NetBull\TranslationBundle\Locale
 */
class CountryMap
{
    /**
     * @var array
     */
    private $map;

    /**
     * @param array $map country map, [country_iso2_code => locale]
     */
    public function __construct(array $map = [])
    {
        $this->map = $map;
    }

    /**
     * @param string $country
     * @return bool|string
     */
    public function getLocale(string $country)
    {
        if (isset($this->map[$country]) && $this->map[$country]) {
            return $this->map[$country];
        }

        return false;
    }
}
