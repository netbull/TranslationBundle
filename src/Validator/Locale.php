<?php

namespace NetBull\TranslationBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class Locale
 * @package NetBull\TranslationBundle\Validator
 */
class Locale extends Constraint
{
    /**
     * @var string
     */
    public $message = 'The locale "%string%" is not a valid locale';

    /**
     * {@inheritDoc}
     */
    public function validatedBy(): string
    {
        return 'netbull_translation.validator.locale';
    }
}
