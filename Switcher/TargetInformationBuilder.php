<?php

namespace NetBull\TranslationBundle\Switcher;

use Symfony\Component\Intl\Intl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Generator\ConfigurableRequirementsInterface;

/**
 * Class TargetInformationBuilder
 * @package NetBull\TranslationBundle\Switcher
 */
class TargetInformationBuilder
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var array
     */
    private $allowedLocales;

    /**
     * @var bool
     */
    private $showCurrentLocale;

    /**
     * @var bool
     */
    private $useController;

    /**
     * TargetInformationBuilder constructor.
     * @param Request $request
     * @param RouterInterface $router
     * @param array $allowedLocales
     * @param bool $showCurrentLocale
     * @param bool $useController
     */
    public function __construct(Request $request, RouterInterface $router, $allowedLocales = [], $showCurrentLocale = false, $useController = false)
    {
        $this->request = $request;
        $this->router = $router;
        $this->allowedLocales = $allowedLocales;
        $this->showCurrentLocale = $showCurrentLocale;
        $this->useController = $useController;
    }

    /**
     * @param null $targetRoute
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public function getTargetInformation($targetRoute = null, $parameters = [])
    {
        $route = $this->request->attributes->get('_route');
        if (method_exists($this->router, 'getGenerator')) {
            $generator = $this->router->getGenerator();
            if ($generator instanceof ConfigurableRequirementsInterface) {
                if (!$generator->isStrictRequirements()) {
                    $strict = false;
                }
            }
        }

        $info['current_locale'] = $this->request->getLocale();
        $info['current_route'] = $route;
        $info['locales'] = [];

        $parameters = array_merge((array) $this->request->attributes->get('_route_params'), $this->request->query->all(), (array) $parameters);

        foreach ($this->allowedLocales as $locale) {
            $strpos = 0 === strpos($this->request->getLocale(), $locale);

            if (($this->showCurrentLocale && $strpos) || !$strpos) {
                $targetLocaleTargetLang = Intl::getLanguageBundle()->getLanguageName($locale, null, $locale);
                $targetLocaleCurrentLang = Intl::getLanguageBundle()->getLanguageName($locale, null, $this->request->getLocale());
                $parameters['_locale'] = $locale;
                try {
                    if (null !== $targetRoute && "" !== $targetRoute) {
                        $switchRoute = $this->router->generate($targetRoute, $parameters);
                    } elseif ($this->useController) {
                        $switchRoute = $this->router->generate('netbull_translation_locale_switcher', ['_locale' => $locale]);
                    } elseif ($route) {
                        $switchRoute = $this->router->generate($route, $parameters);
                    } else {
                        continue;
                    }
                } catch (RouteNotFoundException $e) {
                    // skip routes for which we cannot generate a url for the given locale
                    continue;
                } catch (InvalidParameterException $e) {
                    // skip routes for which we cannot generate a url for the given locale
                    continue;
                } catch (\Exception $e) {
                    if (isset($strict)) {
                        $generator->setStrictRequirements(false);
                    }
                    throw $e;
                }
                $info['locales'][$locale] = [
                    'locale_current_language' => $targetLocaleCurrentLang,
                    'locale_target_language' => $targetLocaleTargetLang,
                    'link' => $switchRoute,
                    'locale' => $locale,
                ];
            }
        }

        if (isset($strict)) {
            $generator->setStrictRequirements(false);
        }
        return $info;
    }
}
