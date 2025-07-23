<?php

namespace NetBull\TranslationBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use NetBull\TranslationBundle\Information\AllowedLocalesProviderInterface;

class LocaleAllowedValidator extends ConstraintValidator
{
    /**
     * @param AllowedLocalesProviderInterface $allowedLocalesProvider
     */
    public function __construct(private AllowedLocalesProviderInterface $allowedLocalesProvider)
    {
    }

    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $locale = (string)$value;

        if (!in_array($locale, $this->getAllowedLocales())) {
            $this->context->addViolation($constraint->message, ['%string%' => $locale]);
        }
    }

    /**
     * @return array
     */
    protected function getAllowedLocales(): array
    {
        if (null !== $this->allowedLocalesProvider) {
            return $this->allowedLocalesProvider->getAllowedLocales();
        } else {
            return [];
        }
    }
}
