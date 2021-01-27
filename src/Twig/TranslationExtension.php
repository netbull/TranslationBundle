<?php

namespace NetBull\TranslationBundle\Twig;

use Exception;
use Locale;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use NetBull\TranslationBundle\Utils\TranslationGuesser;
use NetBull\TranslationBundle\Switcher\TargetInformationBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class TranslationExtension
 * @package NetBull\TranslationBundle\Twig
 */
class TranslationExtension extends AbstractExtension
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
     * @return array|TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('guessTranslation', [$this, 'guessTranslation']),
            new TwigFilter('getTranslation', [$this, 'getTranslation']),
            new TwigFilter('language', [$this, 'languageFromLocale']),
        ];
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('locale_switcher', [$this, 'renderSwitcher'], ['is_safe' => ['html']]),
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
     * @throws Exception
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
    public function guessTranslation(array $translations, $field = 'name', $locale = null, $strict = false): string
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
    public function getTranslation(array $translations, $locale = null, $strict = false): string
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
    public function languageFromLocale($locale, $toLocale = null): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $auto = $request ? $request->getLocale() : 'en';
        $toLocale = ($toLocale)?$toLocale:$auto;
        $language = Locale::getDisplayLanguage($locale, $toLocale);

        return mb_convert_case($language, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * @return string The name of the extension
     */
    public function getName(): string
    {
        return 'translation.extension';
    }
}
