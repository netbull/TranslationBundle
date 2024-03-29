<?php

namespace NetBull\TranslationBundle\Validator;

use Symfony\Component\Intl\Locales;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LocaleValidator extends ConstraintValidator
{
    /**
     * @var bool
     */
    private bool $intlExtension;

    /**
     * @var array
     */
    private array $iso3166;

    /**
     * @var array
     */
    private array $iso639;

    /**
     * @var array
     */
    private array $script;

    /**
     * @param bool $intlExtension Weather the intl extension is installed
     * @param array $iso3166
     * @param array $iso639
     * @param array $script
     */
    public function __construct(bool $intlExtension = false, array $iso3166 = [], array $iso639 = [], array $script = [])
    {
        $this->intlExtension = $intlExtension;
        $this->iso3166 = $iso3166;
        $this->iso639 = $iso639;
        $this->script = $script;
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

        if ($this->intlExtension) {
            $primary = \Locale::getPrimaryLanguage($locale);
            $region  = \Locale::getRegion($locale);
            $locales = Locales::getLocales();

            if ((null !== $region && strtolower($primary) != strtolower($region)) && !in_array($locale, $locales) && !in_array($primary, $locales)) {
                $this->context->addViolation($constraint->message, ['%string%' => $locale]);
            }
        } else {
            $splittedLocale = explode('_', $locale);
            $splitCount = count($splittedLocale);

            if ($splitCount == 1) {
                $primary = $splittedLocale[0];
                if (!in_array($primary, $this->iso639)) {
                    $this->context->addViolation($constraint->message, ['%string%' => $locale]);
                }
            } elseif ($splitCount == 2) {
                $primary = $splittedLocale[0];
                $region = $splittedLocale[1];
                if (!in_array($primary, $this->iso639) && !in_array($region, $this->iso3166)) {
                    $this->context->addViolation($constraint->message, ['%string%' => $locale]);
                }
            } elseif ($splitCount > 2) {
                $primary = $splittedLocale[0];
                $script = $splittedLocale[1];
                $region = $splittedLocale[2];
                if (!in_array($primary, $this->iso639) && !in_array($region, $this->iso3166) && !in_array($script, $this->script)) {
                    $this->context->addViolation($constraint->message, ['%string%' => $locale]);
                }
            }
        }
    }
}
