<?php

namespace NetBull\TranslationBundle\Templating;

use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class LocaleSwitchHelper
 * @package NetBull\TranslationBundle\Templating
 */
class LocaleSwitchHelper extends Helper
{
    protected $templating;

    protected $templates = [
        'links' => 'NetBullTranslationBundle:switcher_links.html.twig',
        'form' => 'NetBullTranslationBundle:switcher_form.html.twig'
    ];

    protected $view;

    /**
     * LocaleSwitchHelper constructor.
     * @param EngineInterface $templating
     * @param $template
     */
    public function __construct(EngineInterface $templating, $template)
    {
        $this->templating = $templating;
        $this->view = array_key_exists($template, $this->templates)
            ? $this->templates[$template] : $template;
    }

    /**
     * @param array $viewParams
     * @param null $template
     * @return string
     */
    public function renderSwitch(array $viewParams = [], $template = null)
    {
        if (!$template) {
            $template = $this->view;
        }

        return $this->templating->render($template, $viewParams);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'locale_switch_helper';
    }
}
