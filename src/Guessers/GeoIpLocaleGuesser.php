<?php

namespace NetBull\TranslationBundle\Guessers;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;
use NetBull\TranslationBundle\Locale\CountryMap;
use Symfony\Component\HttpFoundation\Request;
use NetBull\TranslationBundle\Validator\MetaValidator;

class GeoIpLocaleGuesser extends AbstractLocaleGuesser
{
    /**
     * @var MetaValidator
     */
    private $metaValidator;

    /**
     * @var string
     */
    private $binary;

    /**
     * @var string
     */
    private $default;

    /**
     * @var CountryMap
     */
    private $countryMap;

    /**
     * @var Reader|null
     */
    private $reader;

    /**
     * @param MetaValidator $metaValidator
     * @param string $binary
     * @param string $default
     * @param CountryMap $countryMap
     */
    public function __construct(MetaValidator $metaValidator, string $binary, string $default, CountryMap $countryMap)
    {
        $this->metaValidator = $metaValidator;
        $this->binary = $binary;
        $this->default = $default;
        $this->countryMap = $countryMap;
    }

    /**
     * @return Reader|null
     */
    private function getReader(): Reader
    {
        if (!$this->reader) {
            try {
                $this->reader = new Reader($this->binary);
            } catch (InvalidDatabaseException $e) {}
        }

        return $this->reader;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function guessLocale(Request $request): bool
    {
        if ($this->getReader()) {
            try{
                $record = $this->getReader()->country($request->getClientIp());
                $country = strtolower($record->country->isoCode);
            } catch (AddressNotFoundException | InvalidDatabaseException $e) {
                return false;
            }
        } else {
            return false;
        }

        // see if we have some additional mappings
        if ($this->countryMap->getLocale($country)) {
            $locale = $this->countryMap->getLocale($country);
        } else {
            $locale = $this->default;
        }

        // now validate
        if (false !== $locale && $this->metaValidator->isAllowed($locale)) {
            $this->identifiedLocale = $locale;
            return true;
        }

        return false;
    }
}
