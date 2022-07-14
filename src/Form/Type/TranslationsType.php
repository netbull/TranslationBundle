<?php

namespace NetBull\TranslationBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use NetBull\TranslationBundle\Form\EventListener\TranslationsSubscriber;

class TranslationsType extends AbstractType
{
    const RENDER_TYPE_ROWS = 'rows';
    const RENDER_TYPE_TABS = 'tabs';
    const RENDER_TYPE_TABS_SMALL = 'tabs_small';

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
     * @var array|null
     */
    private $prototypes;

    /**
     * @var string
     */
    private $renderType = self::RENDER_TYPE_ROWS;

    /**
     * @param TranslationsSubscriber $translationsSubscriber
     * @param string $defaultLocale
     * @param array $locales
     */
    public function __construct(TranslationsSubscriber $translationsSubscriber, string $defaultLocale = 'en', array $locales = [])
    {
        $this->translationsSubscriber = $translationsSubscriber;
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @param string $name
     * @param null|FormInterface $prototype
     */
    public function setPrototype(string $name, ?FormInterface $prototype = null)
    {
        $this->prototypes[$name] = $prototype;
    }

    /**
     * @param string $name
     * @return FormInterface|null
     */
    public function getPrototype(string $name): ?FormInterface
    {
        return $this->prototypes[$name] ?? null;
    }

    /**
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->renderType = $options['render_type'];
        $this->translationsSubscriber->setParentForm($this);

        $builder->addEventSubscriber($this->translationsSubscriber);
    }

    /**
     * ToDo: add the trigger rendering into a template...
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

        $prototype = $this->getPrototype($form->getParent()->getName());

        if ($prototype && $options['prototype']) {
            $view->vars['prototype'] = $prototype->setParent($form)->createView($view);
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
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'translations_' . $this->renderType;
    }
}
