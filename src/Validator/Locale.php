<?php

namespace NetBull\TranslationBundle\Validator;

use Symfony\Component\Validator\Constraint;

class Locale extends Constraint
{
    /**
     * @var string
     */
    public string $message = 'The locale "%string%" is not a valid locale';

    /**
     * @return string
     */
    public function validatedBy(): string
    {
        return 'netbull_translation.validator.locale';
    }
}
