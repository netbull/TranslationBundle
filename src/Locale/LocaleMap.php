<?php

namespace NetBull\TranslationBundle\Locale;

/**
 * Class LocaleMap
 * @package NetBull\TranslationBundle\Locale
 */
class LocaleMap
{
    /**
     * @var array
     */
    private $map;

    /**
     * @param array $map topLevelDomain locale map, [tld => locale]
     */
    public function __construct(array $map = [])
    {
        $this->map = $map;
    }

    /**
     * @param string $tld
     * @return bool|string
     */
    public function getLocale(string $tld)
    {
        if (isset($this->map[$tld]) && $this->map[$tld]) {
            return $this->map[$tld];
        }

        return false;
    }
}
