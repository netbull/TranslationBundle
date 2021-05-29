<?php

namespace NetBull\TranslationBundle\Guessers;

use Symfony\Component\HttpFoundation\Request;
use NetBull\TranslationBundle\Validator\MetaValidator;

/**
 * Class RouteParamLocaleGuesser
 * @package NetBull\TranslationBundle\Guessers
 */
class RouteParamLocaleGuesser extends AbstractLocaleGuesser
{
    /**
     * @var MetaValidator
     */
    private $metaValidator;

	/**
	 * RouteParamLocaleGuesser constructor.
	 * @param MetaValidator $metaValidator
	 */
    public function __construct(MetaValidator $metaValidator)
    {
        $this->metaValidator = $metaValidator;
    }

    /**
     * @inheritDoc
     */
    public function guessLocale(Request $request): bool
    {
        $routeParams = $request->attributes->get('_route_params');
        if (empty($routeParams)) {
        	return false;
		}

		$locale = $routeParams['_locale'] ?? false;

        // now validate
        if (false !== $locale && $this->metaValidator->isAllowed($locale)) {
            $this->identifiedLocale = $locale;
            return true;
        }

        return false;
    }
}
