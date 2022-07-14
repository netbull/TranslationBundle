<?php

namespace NetBull\TranslationBundle\Validator;

use Symfony\Component\Validator\Constraint;

class LocaleAllowed extends Constraint
{
    /**
     * @var string
     */
    public string $message = 'The locale "%string%" is not allowed by application configuration.';

    /**
     * @return string
     */
    public function validatedBy(): string
    {
        return 'netbull_translation.validator.locale_allowed';
    }
}
