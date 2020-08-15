<?php

namespace NetBull\TranslationBundle\Validator;

use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class MetaValidator
 * @package NetBull\TranslationBundle\Locale\Validator
 */
class MetaValidator
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * MetaValidator constructor.
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Checks if a locale is allowed and valid
     *
     * @param string $locale
     * @return bool
     */
    public function isAllowed($locale)
    {
        $errorListLocale = $this->validator->validate($locale, new Locale);
        $errorListLocaleAllowed = $this->validator->validate($locale, new LocaleAllowed);

        return (count($errorListLocale) == 0 && count($errorListLocaleAllowed) == 0);
    }
}
