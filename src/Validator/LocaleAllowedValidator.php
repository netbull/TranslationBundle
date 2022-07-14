<?php

namespace NetBull\TranslationBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use NetBull\TranslationBundle\Information\AllowedLocalesProviderInterface;

class LocaleAllowedValidator extends ConstraintValidator
{
    /**
     * @var AllowedLocalesProviderInterface
     */
    private AllowedLocalesProviderInterface $allowedLocalesProvider;

    /**
     * @param AllowedLocalesProviderInterface $allowedLocalesProvider
     */
    public function __construct(AllowedLocalesProviderInterface $allowedLocalesProvider)
    {
        $this->allowedLocalesProvider = $allowedLocalesProvider;
    }

    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
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
