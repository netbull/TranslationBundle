<?php

namespace NetBull\TranslationBundle\Matcher;

use NetBull\TranslationBundle\Information\AllowedLocalesProvider;

class DefaultBestLocaleMatcher implements BestLocaleMatcherInterface
{
    /**
     * @var AllowedLocalesProvider
     */
    private AllowedLocalesProvider $allowedLocaleProvider;

    /**
     * @param AllowedLocalesProvider $allowedLocales
     */
    public function __construct(AllowedLocalesProvider $allowedLocales)
    {
        $this->allowedLocaleProvider = $allowedLocales;
    }

    /**
     * @param string $locale
     * @return string|null
     */
    public function match(string $locale): ?string
    {
        $allowedLocales = $this->allowedLocaleProvider->getAllowedLocales();
        uasort($allowedLocales, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($allowedLocales as $allowedLocale) {
            if (0 === strpos($locale, $allowedLocale)) {
                return $allowedLocale;
            }
        }

        return null;
    }
}
