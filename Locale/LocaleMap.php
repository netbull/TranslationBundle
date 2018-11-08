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
    private $map = [];

    /**
     * @param array $map topLevelDomain locale map, [tld => locale]
     */
    public function __construct(array $map = [])
    {
        $this->map = $map;
    }

    /**
     * @param $tdl
     * @return bool|mixed
     */
    public function getLocale($tdl)
    {
        if (isset($this->map[$tdl]) && $this->map[$tdl]) {
            return $this->map[$tdl];
        }

        return false;
    }
}
