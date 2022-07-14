<?php

namespace NetBull\TranslationBundle\Locale;

class LocaleMap
{
    /**
     * @var array
     */
    private array $map;

    /**
     * @param array $map topLevelDomain locale map, [tld => locale]
     */
    public function __construct(array $map = [])
    {
        $this->map = $map;
    }

    /**
     * @param string $tld
     * @return string|null
     */
    public function getLocale(string $tld): ?string
    {
        if (isset($this->map[$tld]) && $this->map[$tld]) {
            return $this->map[$tld];
        }

        return null;
    }
}
