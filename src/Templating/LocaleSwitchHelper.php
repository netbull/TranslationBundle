<?php

namespace NetBull\TranslationBundle\Templating;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class LocaleSwitchHelper
{
    /**
     * @var Environment
     */
    protected Environment $twig;

    /**
     * @var array
     */
    protected array $templates = [
        'links' => 'NetBullTranslationBundle:switcher_links.html.twig',
        'form' => 'NetBullTranslationBundle:switcher_form.html.twig'
    ];

    /**
     * @var mixed
     */
    protected $view;

    /**
     * @param Environment $twig
     * @param $template
     */
    public function __construct(Environment $twig, $template)
    {
        $this->twig = $twig;
        $this->view = array_key_exists($template, $this->templates) ? $this->templates[$template] : $template;
    }

    /**
     * @param array $viewParams
     * @param null $template
     * @return string|null
     */
    public function renderSwitch(array $viewParams = [], $template = null): ?string
    {
        if (!$template) {
            $template = $this->view;
        }

        try {
            return $this->twig->render($template, $viewParams);
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'locale_switch_helper';
    }
}
