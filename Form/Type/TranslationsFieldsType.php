<?php

namespace NetBull\TranslationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TranslationsFieldsType
 * @package NetBull\TranslationBundle\Form\Type
 */
class TranslationsFieldsType extends AbstractType
{
    /**
     *
     * @param FormBuilderInterface  $builder
     * @param array                 $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['fields'] as $fieldName => $fieldConfig) {
            $fieldType = $fieldConfig['field_type'];
            unset($fieldConfig['field_type']);

            $builder->add($fieldName, $fieldType, array_replace_recursive($fieldConfig, [
                'attr' => [
                    'locale' => $options['locale']
                ]
            ]));
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'fields' => [],
            'locale' => 'en',
        ]);
    }
}
