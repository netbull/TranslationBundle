<?php

namespace NetBull\TranslationBundle\Matcher;

interface BestLocaleMatcherInterface
{
    /**
     * @param string $locale
     * @return string|null
     */
    public function match(string $locale): ?string;
}
