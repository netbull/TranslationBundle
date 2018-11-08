<?php

namespace NetBull\TranslationBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class LocaleAllowed
 * @package NetBull\TranslationBundle\Locale\Validator
 */
class LocaleAllowed extends Constraint
{
    /**
     * @var string
     */
    public $message = 'The locale "%string%" is not allowed by application configuration.';

    /**
     * {@inheritDoc}
     */
    public function validatedBy()
    {
        return 'netbull_translation.validator.locale_allowed';
    }
}
