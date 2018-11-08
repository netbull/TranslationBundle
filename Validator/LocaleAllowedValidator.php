<?php

namespace NetBull\TranslationBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use NetBull\TranslationBundle\Information\AllowedLocalesProviderInterface;

/**
 * Class LocaleAllowedValidator
 * @package NetBull\TranslationBundle\Locale\Validator
 */
class LocaleAllowedValidator extends ConstraintValidator
{
    /**
     * @var AllowedLocalesProviderInterface
     */
    private $allowedLocalesProvider;

    /**
     * LocaleAllowedValidator constructor.
     * @param AllowedLocalesProviderInterface $allowedLocalesProvider
     */
    public function __construct(AllowedLocalesProviderInterface $allowedLocalesProvider)
    {
        $this->allowedLocalesProvider = $allowedLocalesProvider;
    }

    /**
     * @param mixed $locale
     * @param Constraint $constraint
     */
    public function validate($locale, Constraint $constraint)
    {
        if (null === $locale || '' === $locale) {
            return;
        }

        if (!is_scalar($locale) && !(is_object($locale) && method_exists($locale, '__toString'))) {
            throw new UnexpectedTypeException($locale, 'string');
        }

        $locale = (string) $locale;

        if (!in_array($locale, $this->getAllowedLocales())) {
            $this->context->addViolation($constraint->message, ['%string%' => $locale]);
        }
    }

    /**
     * @return array
     */
    protected function getAllowedLocales()
    {
        if (null !== $this->allowedLocalesProvider) {
            return $this->allowedLocalesProvider->getAllowedLocales();
        } else {
            return [];
        }
    }
}
