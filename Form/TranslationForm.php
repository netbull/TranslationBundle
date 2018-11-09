<?php

namespace NetBull\TranslationBundle\Form;

use Doctrine\Common\Persistence\Proxy;
use Symfony\Component\Form\FormRegistry;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormTypeGuesserInterface;

/**
 * Class TranslationForm
 * @package NetBull\TranslationBundle\Form
 */
class TranslationForm
{
    /**
     * @var false|null|\Symfony\Component\Form\FormTypeGuesserChain|\Symfony\Component\Form\FormTypeGuesserInterface
     */
    private $typeGuesser;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * TranslationForm constructor.
     * @param FormRegistry $formRegistry
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(FormRegistry $formRegistry, ManagerRegistry $managerRegistry)
    {
        $this->typeGuesser = $formRegistry->getTypeGuesser();
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param $class
     * @return bool|string
     */
    private function getRealClass($class)
    {
        if (false === $pos = strrpos($class, '\\' . Proxy::MARKER . '\\')) {
            return $class;
        }

        return substr($class, $pos + Proxy::MARKER_LENGTH + 2);
    }

    /**
     * @param $translationClass
     * @param array $exclude
     * @return array
     */
    protected function getTranslationFields($translationClass, array $exclude = [])
    {
        $fields = [];
        $translationClass = $this->getRealClass($translationClass);

        if ($manager = $this->managerRegistry->getManagerForClass($translationClass)) {
            $metadataClass = $manager->getMetadataFactory()->getMetadataFor($translationClass);

            foreach ($metadataClass->fieldMappings as $fieldMapping) {
                if (!in_array($fieldMapping['fieldName'], ['id', 'locale']) && !in_array($fieldMapping['fieldName'], $exclude)) {
                    $fields[] = $fieldMapping['fieldName'];
                }
            }
        }

        return $fields;
    }

    /**
     * @param $class
     * @param $options
     * @return array
     * @throws \Exception
     */
    public function getFieldsOptions($class, $options)
    {
        $fieldsOptions = [];

        foreach ($this->getFieldsList($options, $class) as $field) {
            $fieldOptions = isset($options['fields'][$field]) ? $options['fields'][$field] : [];

            if (!isset($fieldOptions['display']) || $fieldOptions['display']) {
                $fieldOptions = $this->guessMissingFieldOptions($this->typeGuesser, $class, $field, $fieldOptions);

                // Custom options by locale
                if (isset($fieldOptions['locale_options'])) {
                    $localesFieldOptions = $fieldOptions['locale_options'];
                    unset($fieldOptions['locale_options']);

                    foreach ($options['locales'] as $locale) {
                        $localeFieldOptions = isset($localesFieldOptions[$locale]) ? $localesFieldOptions[$locale] : [];
                        if (!isset($localeFieldOptions['display']) || $localeFieldOptions['display']) {
                            $fieldsOptions[$locale][$field] = $localeFieldOptions + $fieldOptions;
                        }
                    }

                    // General options for all locales
                } else {
                    foreach ($options['locales'] as $locale) {
                        $fieldsOptions[$locale][$field] = $fieldOptions;
                    }
                }
            }
        }

        return $fieldsOptions;
    }

    /**
     * @param $class
     * @param $options
     * @return array
     * @throws \Exception
     */
    public function getPrototypeFieldsOptions($class, $options)
    {
        $fieldsOptions = [];

        foreach ($this->getFieldsList($options, $class) as $field) {
            $fieldOptions = isset($options['fields'][$field]) ? $options['fields'][$field] : [];

            if (!isset($fieldOptions['display']) || $fieldOptions['display']) {
                $fieldOptions = $this->guessMissingFieldOptions($this->typeGuesser, $class, $field, $fieldOptions);

                // Custom options by locale
                if (isset($fieldOptions['locale_options'])) {
                    $localesFieldOptions = $fieldOptions['locale_options'];
                    unset($fieldOptions['locale_options']);

                    $localeFieldOptions = $localesFieldOptions ? $localesFieldOptions : [];
                    if (!isset($localeFieldOptions['display']) || $localeFieldOptions['display']) {
                        $fieldsOptions[$field] = $localeFieldOptions + $fieldOptions;
                    }

                    // General options for all locales
                } else {
                    $fieldsOptions[$field] = $fieldOptions;
                }
            }
        }

        return $fieldsOptions;
    }

    /**
     * Combine formFields with translationFields. (Useful for upload field)
     * @param $options
     * @param $class
     * @return array
     * @throws \Exception
     */
    private function getFieldsList($options, $class)
    {
        $formFields = array_keys($options['fields']);

        // Check existing
        foreach ($formFields as $field) {
            if (!property_exists($class, $field)) {
                throw new \Exception("Field '". $field ."' doesn't exist in ". $class);
            }
        }

        return array_unique(array_merge($formFields, $this->getTranslationFields($class, $options['exclude_fields'])));
    }

    /**
     * {@inheritdoc}
     */
    public function getFormsOptions($options)
    {
        $formsOptions = [];

        // Current options
        $formOptions = $options['form_options'];

        // Custom options by locale
        if (isset($formOptions['locale_options'])) {
            $localesFormOptions = $formOptions['locale_options'];
            unset($formOptions['locale_options']);

            foreach ($options['locales'] as $locale) {
                $localeFormOptions = isset($localesFormOptions[$locale]) ? $localesFormOptions[$locale] : [];
                if (!isset($localeFormOptions['display']) || $localeFormOptions['display']) {
                    $formsOptions[$locale] = $localeFormOptions + $formOptions;
                }
            }

            // General options for all locales
        } else {
            foreach ($options['locales'] as $locale) {
                $formsOptions[$locale] = $formOptions;
            }
        }

        return $formsOptions;
    }

    /**
     * @param FormTypeGuesserInterface $guesser
     * @param $class
     * @param $property
     * @param $options
     * @return mixed
     */
    public function guessMissingFieldOptions(FormTypeGuesserInterface $guesser, $class, $property, $options)
    {
        if (!isset($options['field_type']) && ($typeGuess = $guesser->guessType($class, $property))) {
            $options['field_type'] = $typeGuess->getType();
        }

        if (!isset($options['pattern']) && ($patternGuess = $guesser->guessPattern($class, $property))) {
            $options['pattern'] = $patternGuess->getValue();
        }

        return $options;
    }
}
