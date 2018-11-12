<?php

namespace NetBull\TranslationBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use NetBull\TranslationBundle\Form\EventListener\TranslationsSubscriber;

/**
 * Class TranslationsType
 * @package NetBull\TranslationBundle\Form\Type
 */
class TranslationsType extends AbstractType
{
    const RENDER_TYPE_ROWS = 'rows';
    const RENDER_TYPE_TABS = 'tabs';
    const RENDER_TYPE_TABS_SMALL = 'tabs-small';

    /**
     * @var TranslationsSubscriber
     */
    private $translationsSubscriber;

    /**
     * @var array
     */
    private $locales;

    /**
     * @var array
     */
    private $defaultLocale;

    /**
     * @var array
     */
    private $renderTypes = [self::RENDER_TYPE_ROWS, self::RENDER_TYPE_TABS, self::RENDER_TYPE_TABS_SMALL];

    /**
     * @var FormInterface|null
     */
    private $prototype;

    /**
     * @var string
     */
    private $renderType = self::RENDER_TYPE_ROWS;

    /**
     * TranslationsType constructor.
     * @param TranslationsSubscriber $translationsSubscriber
     * @param array $locales
     * @param string $defaultLocale
     */
    public function __construct(TranslationsSubscriber $translationsSubscriber, $locales = [], $defaultLocale = 'en')
    {
        $this->translationsSubscriber = $translationsSubscriber;
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @param FormInterface $prototype
     */
    public function setPrototype(FormInterface $prototype)
    {
        $this->prototype = $prototype;
    }

    /**
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->translationsSubscriber->setParentForm($this);
        $builder->addEventSubscriber($this->translationsSubscriber);
    }

    /**
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['default_locale'] = $options['default_locale'];
        $view->vars['required_locales'] = $options['required_locales'];
        $view->vars['render_type'] = in_array($options['render_type'], $this->renderTypes) ? $options['render_type'] : self::RENDER_TYPE_TABS;
        $this->renderType = $view->vars['render_type'];

        if ($this->prototype) {
            $view->vars['prototype'] = $this->prototype->setParent($form)->createView($view);
            if (in_array($options['render_type'], [self::RENDER_TYPE_TABS, self::RENDER_TYPE_TABS_SMALL])) {
                $view->vars['prototype_trigger'] = '<li class="nav-item" data-translation="__locale__"><a class="nav-link" data-toggle="tab" href="#'. $view->vars['id'] .'___locale__"><i class="flag flag-icon-__locale__ mr-1 no-translation"></i>';

                if (self::RENDER_TYPE_TABS === $options['render_type']) {
                    $view->vars['prototype_trigger'] .= '<span class=".d-none .d-lg-block .d-xl-none">__label__</span>';
                }

                $view->vars['prototype_trigger'] .= '</a></li>';
            }
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'by_reference' => false,
            'empty_data' => function () {
                return new ArrayCollection();
            },
            'locales' => $this->locales,
            'default_locale' => $this->defaultLocale,
            'required_locales' => [$this->defaultLocale],
            'fields' => [],
            'exclude_fields' => [],
            'render_type' => self::RENDER_TYPE_TABS,
            'prototype' => false,
        ]);

        $resolver->setAllowedValues('render_type', $this->renderTypes);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'translations_' . $this->renderType;
    }
}
