<?php

namespace NetBull\TranslationBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use NetBull\TranslationBundle\ORM\Subscribers\Translation\TranslationInterface;

class TranslationsFieldsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
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
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['empty'] = self::isTranslationEmpty($form);
    }

    /**
     * @param FormInterface $form
     * @return bool
     */
    public static function isTranslationEmpty(FormInterface $form): bool
    {
        $empty = true;
        foreach ($form as $fieldForm) {
            if ($fieldForm->getData()) {
                $empty = false;
                break;
            }
        }

        return $empty;
    }

    /**
     * @param mixed $payload
     * @param ExecutionContextInterface $context
     */
    public function validate($payload, ExecutionContextInterface $context)
    {
        /** @var FormInterface $form **/
        $form = $context->getObject();

        if (!self::isTranslationEmpty($form) && $payload instanceof TranslationInterface) {
            foreach ($context->getObject() as $field => $fieldForm) {
                if (in_array($field, $payload->getMandatoryFields()) && !$fieldForm->getData()) {
                    $context->buildViolation(sprintf('Field "%s" should not be blank.', ucfirst($field)))
                        ->atPath($field)
                        ->addViolation();
                }
            }
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
            'constraints' => new Callback(['callback' => [$this, 'validate']])
        ]);
    }
}
