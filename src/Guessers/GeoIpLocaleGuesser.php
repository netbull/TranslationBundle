<?php

namespace NetBull\TranslationBundle\Guessers;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;
use NetBull\TranslationBundle\Locale\CountryMap;
use Symfony\Component\HttpFoundation\Request;
use NetBull\TranslationBundle\Validator\MetaValidator;

/**
 * Class GeoIpLocaleGuesser
 * @package NetBull\TranslationBundle\Guessers
 */
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
     * @var CountryMap
     */
    private $countryMap;

    /**
     * @var Reader|null
     */
    private $reader;

    /**
     * CountryLocaleGuesser constructor.
     * @param MetaValidator $metaValidator
     * @param string $binary
     * @param CountryMap $countryMap
     */
    public function __construct(MetaValidator $metaValidator, string $binary, CountryMap $countryMap)
    {
        $this->metaValidator = $metaValidator;
        $this->binary = $binary;
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
     * @inheritDoc
     */
    public function guessLocale(Request $request): bool
    {
        if ($this->reader) {
            try{
                $record = $this->reader->country($request->getClientIp());
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
            return false;
        }

        // now validate
        if (false !== $locale && $this->metaValidator->isAllowed($locale)) {
            $this->identifiedLocale = $locale;
            return true;
        }

        return false;
    }
}
