<?php

namespace NetBull\TranslationBundle\Switcher;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Generator\ConfigurableRequirementsInterface;

class TargetInformationBuilder
{
    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var RouterInterface
     */
    private RouterInterface $router;

    /**
     * @var array
     */
    private array $allowedLocales;

    /**
     * @var bool
     */
    private bool $showCurrentLocale;

    /**
     * @var bool
     */
    private bool $useController;

    /**
     * @param Request $request
     * @param RouterInterface $router
     * @param array $allowedLocales
     * @param bool $showCurrentLocale
     * @param bool $useController
     */
    public function __construct(Request $request, RouterInterface $router, array $allowedLocales = [], bool $showCurrentLocale = false, bool $useController = false)
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
     * @throws Exception
     */
    public function getTargetInformation($targetRoute = null, array $parameters = [])
    {
        $route = $this->request->attributes->get('_route');
        $generator = null;
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

        foreach ($this->allowedLocales as $locale) {
            $strpos = 0 === strpos($this->request->getLocale(), $locale);

            if (($this->showCurrentLocale && $strpos) || !$strpos) {
                $targetLocaleTargetLang = Languages::getName($locale, $locale);
                $targetLocaleCurrentLang = Languages::getName($locale, $this->request->getLocale());

                if ($info['current_locale'] === $locale) { // If this locale is active, avoid generating a link, it's not needed anyway
                    $url = 'javascript:';
                } else {
                    $parameters['_locale'] = $locale;
                    try {
                        if (null !== $targetRoute && "" !== $targetRoute) {
                            $url = $this->router->generate($targetRoute, $parameters);
                        } elseif ($this->useController) {
                            $url = $this->router->generate('netbull_translation_locale_switcher', ['_locale' => $locale]);
                        } elseif ($route) {
                            $url = $this->router->generate($route, $parameters);
                        } else {
                            continue;
                        }
                    } catch (RouteNotFoundException $e) {
                        // skip routes for which we cannot generate a url for the given locale
                        continue;
                    } catch (InvalidParameterException $e) {
                        // skip routes for which we cannot generate a url for the given locale
                        continue;
                    } catch (Exception $e) {
                        if (isset($strict) && $generator) {
                            $generator->setStrictRequirements(false);
                        }
                        throw $e;
                    }
                }

                $info['locales'][$locale] = [
                    'locale_current_language' => $targetLocaleCurrentLang,
                    'locale_target_language' => $targetLocaleTargetLang,
                    'link' => $url,
                    'locale' => $locale,
                ];
            }
        }

        if (isset($strict) && $generator) {
            $generator->setStrictRequirements(false);
        }
        return $info;
    }
}
