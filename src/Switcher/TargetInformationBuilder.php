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
     * @param Request $request
     * @param RouterInterface $router
     * @param array $allowedLocales
     * @param bool $showCurrentLocale
     * @param bool $useController
     */
    public function __construct(
        private Request $request,
        private RouterInterface $router,
        private array $allowedLocales = [],
        private bool $showCurrentLocale = false,
        private bool $useController = false
    ) {
    }

    /**
     * @param $targetRoute
     * @param array $parameters
     * @return array
     * @throws Exception
     */
    public function getTargetInformation($targetRoute = null, array $parameters = []): array
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
            $strpos = str_starts_with($this->request->getLocale(), $locale);

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
                    } catch (RouteNotFoundException | InvalidParameterException) {
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
