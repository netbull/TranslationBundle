<?php

namespace NetBull\TranslationBundle\Matcher;

use NetBull\TranslationBundle\Information\AllowedLocalesProvider;

class DefaultBestLocaleMatcher implements BestLocaleMatcherInterface
{
    /**
     * @param AllowedLocalesProvider $allowedLocaleProvider
     */
    public function __construct(private AllowedLocalesProvider $allowedLocaleProvider)
    {
    }

    /**
     * @param string $locale
     * @return string|null
     */
    public function match(string $locale): ?string
    {
        $allowedLocales = $this->allowedLocaleProvider->getAllowedLocales();
        uasort($allowedLocales, fn ($a, $b) => strlen($b) - strlen($a));

        foreach ($allowedLocales as $allowedLocale) {
            if (str_starts_with($locale, $allowedLocale)) {
                return $allowedLocale;
            }
        }

        return null;
    }
}
