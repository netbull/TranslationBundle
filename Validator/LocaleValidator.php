<?php

namespace NetBull\TranslationBundle\Validator;

use Symfony\Component\Intl\Intl;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class LocaleValidator
 * @package NetBull\TranslationBundle\Locale\Validator
 */
class LocaleValidator extends ConstraintValidator
{
    /**
     * @var bool
     */
    private $intlExtension;

    /**
     * @var array
     */
    private $iso3166;

    /**
     * @var array
     */
    private $iso639;

    /**
     * @var array
     */
    private $script;

    /**
     * Constructor
     *
     * @param bool  $intlExtension Weather the intl extension is installed
     * @param array $iso3166 Array of valid iso3166 codes
     * @param array $iso639 Array of valid iso639 codes
     * @param array $script Array of valid locale scripts
     */
    public function __construct($intlExtension = false, array $iso3166 = [], array $iso639 = [], array $script = [])
    {
        $this->intlExtension = $intlExtension;
        $this->iso3166 = $iso3166;
        $this->iso639 = $iso639;
        $this->script = $script;
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

        if ($this->intlExtension) {
            $primary = \Locale::getPrimaryLanguage($locale);
            $region  = \Locale::getRegion($locale);
            $locales = Intl::getLocaleBundle()->getLocales();

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
