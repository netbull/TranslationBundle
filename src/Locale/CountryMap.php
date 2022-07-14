<?php

namespace NetBull\TranslationBundle\Locale;

class CountryMap
{
    /**
     * @var array
     */
    private array $map;

    /**
     * @param array $map country map, [country_iso2_code => locale]
     */
    public function __construct(array $map = [])
    {
        $this->map = $map;
    }

    /**
     * @param string $country
     * @return string|null
     */
    public function getLocale(string $country): ?string
    {
        if (isset($this->map[$country]) && $this->map[$country]) {
            return $this->map[$country];
        }

        return null;
    }
}
