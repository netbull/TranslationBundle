<?php

namespace NetBull\TranslationBundle\Utils;

/**
 * Class TranslationGuesser
 * @package NetBull\TranslationBundle\Utils
 */
class TranslationGuesser
{
    /**
     * @param array $array
     * @param $field
     * @param string $locale
     * @param false $strict
     * @return array|string|null
     */
    public static function guess(array $array, $field, $locale = 'en', $strict = false)
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
     * @param false $strict
     * @return array|null
     */
    public static function get(array $array, $locale = 'en', $strict = false): ?array
    {
        if (empty($array)) {
            return null;
        }

        if (isset($array[$locale])) {
            return $array[$locale];
        } else if (!$strict) {
            if (isset($array['en'])) {
                return $array['en'];
            } else {
                return array_values($array)[0];
            }
        }

        return null;
    }
}
