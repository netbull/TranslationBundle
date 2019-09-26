<?php

namespace NetBull\TranslationBundle\Event;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class FilterLocaleSwitchEvent
 * @package NetBull\TranslationBundle\Event
 */
class FilterLocaleSwitchEvent extends Event
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $locale;

    /**
     * FilterLocaleSwitchEvent constructor.
     * @param Request $request
     * @param string $locale
     */
    public function __construct(Request $request, string $locale)
    {
        if (!is_string($locale) || null === $locale || '' === $locale) {
            throw new InvalidArgumentException(sprintf('Wrong type, expected "string" got "%s"', $locale));
        }

        $this->request = $request;
        $this->locale = $locale;
    }

    /**
     * Returns the request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the locale string
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
