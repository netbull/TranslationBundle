<?php

namespace NetBull\TranslationBundle\Form\EventListener;

use Exception;
use Locale;
use Symfony\Component\Form;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use NetBull\TranslationBundle\Form\TranslationForm;
use NetBull\TranslationBundle\Form\Type\TranslationsType;
use NetBull\TranslationBundle\Form\Type\TranslationsFieldsType;

class TranslationsSubscriber implements EventSubscriberInterface
{
    /**
     * @var TranslationForm
     */
    private TranslationForm $translationForm;

    /**
     * @var Form\FormFactoryInterface
     */
    private Form\FormFactoryInterface $formFactory;

    /**
     * @var TranslationsType|null
     */
    private ?TranslationsType $parentForm = null;

    /**
     * @param TranslationForm $translationForm
     * @param Form\FormFactoryInterface $formFactory
     */
    public function __construct(TranslationForm $translationForm, Form\FormFactoryInterface $formFactory)
    {
        $this->translationForm = $translationForm;
        $this->formFactory = $formFactory;
    }

    /**
     * @param TranslationsType $form
     */
    public function setParentForm(TranslationsType $form)
    {
        $this->parentForm = $form;
    }

    /**
     * @param Form\FormEvent $event
     * @return void
     * @throws Exception
     */
    public function preSetData(Form\FormEvent $event)
    {
        $form = $event->getForm();

        $translatableClass = $form->getParent()->getConfig()->getDataClass();
        $translationClass = $this->getTranslationClass($translatableClass);

        $formOptions = $form->getConfig()->getOptions();
        $fieldsOptions = $this->translationForm->getFieldsOptions($translationClass, $formOptions);

        if (isset($formOptions['locales'])) {
            foreach ($formOptions['locales'] as $locale) {
                if (isset($fieldsOptions[$locale])) {
                    $form->add($locale, TranslationsFieldsType::class, [
                        'label' => $formOptions['render_type'] === TranslationsType::RENDER_TYPE_ROWS ? false : $locale,
                        'data_class' => $translationClass,
                        'fields' => $fieldsOptions[$locale],
                        'locale' => $locale,
                        'required' => in_array($locale, $formOptions['required_locales']),
                    ]);
                }
            }
        }

        $formName = $form->getParent()->getName();
        if (isset($formOptions['prototype']) && $formOptions['prototype'] && !$this->parentForm->getPrototype($formName)) {
            $options = [
                'data_class' => $translationClass,
                'fields' => $this->translationForm->getPrototypeFieldsOptions($translationClass, $formOptions),
                'locale' => '__locale__',
                'required' => false,
            ];

            $builder = $this->formFactory->createNamedBuilder('__locale__', TranslationsFieldsType::class, null, $options);
            $this->parentForm->setPrototype($formName, $builder->getForm());
        }
    }

    /**
     * @param Form\FormEvent $event
     */
    public function submit(Form\FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        /**
         * @var string $locale
         * @var Form\FormInterface $translationForm
         */
        foreach ($form as $locale => $translationForm) {
            $translation = $translationForm->getData();

            // Remove useless Translation object
            if (!$translation || (TranslationsFieldsType::isTranslationEmpty($translationForm) && !$translationForm->getConfig()->getOption('required'))) {
                $data->removeElement($translation);
                continue;
            }

            if (TranslationsFieldsType::isTranslationEmpty($translationForm) && $translationForm->getConfig()->getOption('required')) {
                $translationForm->addError(new Form\FormError(sprintf('Language "%s" should not be blank.', Locale::getDisplayLanguage($locale, 'en'))));
            }

            $translation->setLocale($locale);
        }
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Form\FormEvents::PRE_SET_DATA => 'preSetData',
            Form\FormEvents::POST_SUBMIT => 'submit',
        ];
    }

    /**
     * @param mixed $translatableClass
     * @return string
     */
    private function getTranslationClass($translatableClass): string
    {
        if (method_exists($translatableClass, 'getTranslationEntityClass')) {
            return $translatableClass::getTranslationEntityClass();
        }

        return $translatableClass .'Translation';
    }
}
