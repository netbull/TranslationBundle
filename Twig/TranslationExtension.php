<?php

namespace NetBull\TranslationBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;

use NetBull\TranslationBundle\Utils\TranslationGuesser;
use NetBull\TranslationBundle\Switcher\TargetInformationBuilder;

/**
 * Class TranslationExtension
 * @package NetBull\TranslationBundle\Twig
 */
class TranslationExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * TranslationExtension constructor.
     * @param ContainerInterface $container
     * @param RequestStack $requestStack
     */
    public function __construct(ContainerInterface $container, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
    }

    /**
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('guessTranslation', [$this, 'guessTranslation']),
            new \Twig_SimpleFilter('getTranslation', [$this, 'getTranslation']),
            new \Twig_SimpleFilter('language', [$this, 'languageFromLocale']),
        ];
    }

    /**
     * @return array The added functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('locale_switcher', [$this, 'renderSwitcher'], ['is_safe' => ['html']]),
        ];
    }

    #########################################
    #              Functions                #
    #########################################

    /**
     * @param null $template
     * @param array $parameters
     * @param null $route
     * @return mixed
     * @throws \Exception
     */
    public function renderSwitcher($template = null, $parameters = [], $route = null)
    {
        if (!$route) {
            $route = $this->container->getParameter('netbull_translation.switcher.route');
        }

        $showCurrentLocale = $this->container->getParameter('netbull_translation.switcher.show_current_locale');
        $useController = $this->container->getParameter('netbull_translation.switcher.use_controller');
        $allowedLocales = $this->container->get('netbull_translation.allowed_locales_provider')->getAllowedLocales();
        $request = $this->container->get('request_stack')->getMasterRequest();
        $infoBuilder = new TargetInformationBuilder($request, $this->container->get('router'), $allowedLocales, $showCurrentLocale, $useController);
        $info = $infoBuilder->getTargetInformation($route, $parameters);

        return $this->container->get('netbull_translation.locale_switcher_helper')->renderSwitch($info, $template);
    }

    #########################################
    #                Filters                #
    #########################################

    /**
     * @param array $translations
     * @param string $field
     * @param null $locale
     * @param bool $strict
     * @return mixed|string
     */
    public function guessTranslation(array $translations, $field = 'name', $locale = null, $strict = false)
    {
        if (empty($translations)) {
            return '';
        }

        if (!$locale) {
            $locale = $this->requestStack->getCurrentRequest()->getLocale();
        }

        return TranslationGuesser::guess($translations, $field, $locale, $strict);
    }

    /**
     * @param array $translations
     * @param null $locale
     * @param bool $strict
     * @return mixed|string
     */
    public function getTranslation(array $translations, $locale = null, $strict = false)
    {
        if (empty($translations)) {
            return '';
        }

        if (!$locale) {
            $locale = $this->requestStack->getCurrentRequest()->getLocale();
        }

        return TranslationGuesser::get($translations, $locale, $strict);
    }

    /**
     * Return Language representation for a given Locale
     * @param $locale
     * @param string $toLocale
     * @return string
     */
    public function languageFromLocale($locale, $toLocale = null)
    {
        $request = $this->requestStack->getCurrentRequest();
        $auto = $request ? $request->getLocale() : 'en';
        $toLocale = ($toLocale)?$toLocale:$auto;
        $language = \Locale::getDisplayLanguage($locale, $toLocale);

        return mb_convert_case($language, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * @return string The name of the extension
     */
    public function getName()
    {
        return 'translation.extension';
    }
}
