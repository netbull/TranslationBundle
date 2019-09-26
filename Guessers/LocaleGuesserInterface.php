<?php

namespace NetBull\TranslationBundle\Guessers;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface LocaleGuesserInterface
 * @package NetBull\TranslationBundle\Guessers
 */
interface LocaleGuesserInterface
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    public function guessLocale(Request $request);

    /**
     * @return mixed
     */
    public function getIdentifiedLocale();
}
