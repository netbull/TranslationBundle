<?php

namespace NetBull\TranslationBundle\Guessers;

use Symfony\Component\HttpFoundation\Request;

interface LocaleGuesserInterface
{
    /**
     * @param Request $request
     * @return bool
     */
    public function guessLocale(Request $request): bool;

    /**
     * @return string|null
     */
    public function getIdentifiedLocale(): ?string;
}
