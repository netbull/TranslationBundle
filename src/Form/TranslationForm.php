<?php

namespace NetBull\TranslationBundle\Form;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\Proxy;
use Exception;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\FormTypeGuesserChain;
use Symfony\Component\Form\FormTypeGuesserInterface;

class TranslationForm
{
    /**
     * @var mixed|FormTypeGuesserChain|FormTypeGuesserInterface
     */
    private $typeGuesser;

    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @param FormRegistry $formRegistry
     * @param Registry $registry
     */
    public function __construct(FormRegistry $formRegistry, Registry $registry)
    {
        $this->typeGuesser = $formRegistry->getTypeGuesser();
        $this->registry = $registry;
    }

    /**
     * @param string $class
     * @return bool|string
     */
    private function getRealClass(string $class)
    {
        if (false === $pos = strrpos($class, '\\' . Proxy::MARKER . '\\')) {
            return $class;
        }

        return substr($class, $pos + Proxy::MARKER_LENGTH + 2);
    }

    /**
     * @param string $translationClass
     * @param array $exclude
     * @return array
     */
    protected function getTranslationFields(string $translationClass, array $exclude = []): array
    {
        $fields = [];
        $translationClass = $this->getRealClass($translationClass);

        if ($manager = $this->registry->getManagerForClass($translationClass)) {
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
     * @param string $class
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function getFieldsOptions(string $class, array $options): array
    {
        $fieldsOptions = [];

        foreach ($this->getFieldsList($options, $class) as $field) {
            $fieldOptions = $options['fields'][$field] ?? [];

            if (!isset($fieldOptions['display']) || $fieldOptions['display']) {
                $fieldOptions = $this->guessMissingFieldOptions($this->typeGuesser, $class, $field, $fieldOptions);

                // Custom options by locale
                if (isset($fieldOptions['locale_options'])) {
                    $localesFieldOptions = $fieldOptions['locale_options'];
                    unset($fieldOptions['locale_options']);

                    foreach ($options['locales'] as $locale) {
                        $localeFieldOptions = $localesFieldOptions[$locale] ?? [];
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
     * @param string $class
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function getPrototypeFieldsOptions(string $class, array $options): array
    {
        $fieldsOptions = [];

        foreach ($this->getFieldsList($options, $class) as $field) {
            $fieldOptions = $options['fields'][$field] ?? [];

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
     * @param array $options
     * @param string $class
     * @return array
     * @throws Exception
     */
    private function getFieldsList(array $options, string $class): array
    {
        $formFields = array_keys($options['fields']);

        // Check existing
        foreach ($formFields as $field) {
            if (!property_exists($class, $field)) {
                throw new Exception("Field '". $field ."' doesn't exist in ". $class);
            }
        }

        return array_unique(array_merge($formFields, $this->getTranslationFields($class, $options['exclude_fields'])));
    }

    /**
     * @param array $options
     * @return array
     */
    public function getFormsOptions(array $options): array
    {
        $formsOptions = [];

        // Current options
        $formOptions = $options['form_options'];

        // Custom options by locale
        if (isset($formOptions['locale_options'])) {
            $localesFormOptions = $formOptions['locale_options'];
            unset($formOptions['locale_options']);

            foreach ($options['locales'] as $locale) {
                $localeFormOptions = $localesFormOptions[$locale] ?? [];
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
     * @param string $class
     * @param string $property
     * @param array $options
     * @return array
     */
    public function guessMissingFieldOptions(FormTypeGuesserInterface $guesser, string $class, string $property, array $options): array
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
