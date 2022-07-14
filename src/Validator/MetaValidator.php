<?php

namespace NetBull\TranslationBundle\Validator;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class MetaValidator
{
    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param string $locale
     * @return bool
     */
    public function isAllowed(string $locale): bool
    {
        $errorListLocale = $this->validator->validate($locale, new Locale);
        $errorListLocaleAllowed = $this->validator->validate($locale, new LocaleAllowed);

        return (0 === count($errorListLocale) && 0 === count($errorListLocaleAllowed));
    }
}
