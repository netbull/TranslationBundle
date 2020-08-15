<?php

namespace NetBull\TranslationBundle\Matcher;

/**
 * Interface BestLocaleMatcherInterface
 * @package NetBull\TranslationBundle\Matcher
 */
interface BestLocaleMatcherInterface
{
    /**
     * @param $locale
     * @return mixed
     */
    public function match($locale);
}
