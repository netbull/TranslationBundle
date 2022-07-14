<?php

namespace NetBull\TranslationBundle\Utils;

class TranslationGuesser
{
    /**
     * @param array $array
     * @param string $field
     * @param string $locale
     * @param bool $strict
     * @return mixed|null
     */
    public static function guess(array $array, string $field, string $locale = 'en', bool $strict = false)
    {
        if (empty($array)) {
            return null;
        }

        if (array_keys($array) !== range(0, count($array) - 1)) {
            if (isset($array[$locale])) {
                return $array[$locale][$field];
            } else if (!$strict) {
                if (isset($array['en'])) {
                    return $array['en'][$field];
                } else {
                    return array_values($array)[0][$field];
                }
            }
        } else {
            $tmp = null;
            foreach ($array as $arr) {
                if (isset($arr['locale']) && $arr['locale'] == $locale) {
                    $tmp = $arr;
                }
            }

            if (isset($tmp[$field])) {
                return $tmp[$field];
            }
        }

        return null;
    }

    /**
     * @param array $translations
     * @param string $field
     * @param string $locale
     * @param string $defaultLocale
     * @return string|null
     */
    public static function guessFallback(array $translations, string $field, string $locale, string $defaultLocale): ?string
    {
        if (empty($translations)) {
            return null;
        }

        $translation = $translations[$locale] ?? null;
        $defaultTranslation = $translations[$defaultLocale] ?? null;

        if ($translation && !empty($translation[$field])) {
            return $translation[$field];
        }

        if ($defaultTranslation && !empty($defaultTranslation[$field])) {
            return $defaultTranslation[$field];
        }

        return null;
    }

    /**
     * @param array $array
     * @param string $locale
     * @param bool $strict
     * @return array|null
     */
    public static function get(array $array, string $locale = 'en', bool $strict = false): ?array
    {
        if (empty($array)) {
            return null;
        }

        if (isset($array[$locale])) {
            return $array[$locale];
        } else if (!$strict) {
            return $array['en'] ?? array_values($array)[0];
        }

        return null;
    }
}
