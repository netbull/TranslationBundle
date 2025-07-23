<?php

namespace NetBull\TranslationBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class FilterLocaleSwitchEvent extends Event
{
    public const NAME = 'netbull_translation.locale.change';

    /**
     * @param Request $request
     * @param string $locale
     */
    public function __construct(
        protected Request $request,
        protected string $locale
    ) {
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }
}
