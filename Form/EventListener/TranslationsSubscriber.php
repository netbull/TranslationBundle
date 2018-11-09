<?php

namespace NetBull\TranslationBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use NetBull\TranslationBundle\Form\Type\TranslationsType;
use NetBull\TranslationBundle\Form\TranslationForm;
use NetBull\TranslationBundle\Form\Type\TranslationsFieldsType;
use NetBull\TranslationBundle\ORM\Subscribers\Translation\TranslationInterface;

/**
 * Class TranslationsSubscriber
 * @package NetBull\TranslationBundle\Form\EventListener
 */
class TranslationsSubscriber implements EventSubscriberInterface
{
    /**
     * @var TranslationForm
     */
    private $translationForm;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var TranslationsType
     */
    private $parentForm;

    /**
     * TranslationsSubscriber constructor.
     * @param TranslationForm $translationForm
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(TranslationForm $translationForm, FormFactoryInterface $formFactory)
    {
        $this->translationForm = $translationForm;
        $this->formFactory = $formFactory;
    }

    /**
     * @param $form
     */
    public function setParentForm($form)
    {
        $this->parentForm = $form;
    }

    /**
     * {@inheritdoc}
     */
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();

        $translatableClass = $form->getParent()->getConfig()->getDataClass();
        $translationClass = $this->getTranslationClass($translatableClass);

        $formOptions = $form->getConfig()->getOptions();
        $fieldsOptions = $this->translationForm->getFieldsOptions($translationClass, $formOptions);

        if (isset($formOptions['locales'])) {
            foreach ($formOptions['locales'] as $locale) {
                if (isset($fieldsOptions[$locale])) {
                    $form->add(
                        $locale,
                        TranslationsFieldsType::class,
                        [
                            'data_class' => $translationClass,
                            'fields' => $fieldsOptions[$locale],
                            'locale' => $locale,
                            'required' => in_array($locale, $formOptions['required_locales']),
                        ]
                    );
                }
            }
        }

        if (isset($formOptions['prototype']) && $formOptions['prototype']) {
            $options = [
                'data_class' => $translationClass,
                'fields' => $this->translationForm->getPrototypeFieldsOptions($translationClass, $formOptions),
                'locale' => '__locale__',
                'required' => false,
            ];

            switch ($formOptions['render_type']) {
                case TranslationsType::RENDER_TYPE_ROWS:

                    break;
                case TranslationsType::RENDER_TYPE_TABS:
                case TranslationsType::RENDER_TYPE_TABS_SMALL:
                    $builder = $this->formFactory->createNamedBuilder('__locale__', TranslationsFieldsType::class, null, $options);
                    $this->parentForm->setPrototype($builder->getForm());
                    break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submit(FormEvent $event)
    {
        $data = $event->getData();

        foreach ($data as $locale => $translation) {
            // Remove useless Translation object
            if (!$translation) {
                $data->removeElement($translation);
            } else {
                $translation->setLocale($locale);
            }

            if ($translation instanceof TranslationInterface) {
                if ($translation->isEmpty()) {
                    $data->removeElement($translation);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::SUBMIT => 'submit',
        ];
    }

    /**
     * @param $translatableClass
     * @return string
     */
    private function getTranslationClass($translatableClass)
    {
        if (method_exists($translatableClass, 'getTranslationEntityClass')) {
            return $translatableClass::getTranslationEntityClass();
        }

        return $translatableClass .'Translation';
    }
}
