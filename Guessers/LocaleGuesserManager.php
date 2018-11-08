<?php

namespace NetBull\TranslationBundle\Guessers;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class LocaleGuesserManager
 * @package NetBull\TranslationBundle\Guessers
 */
class LocaleGuesserManager
{
    /**
     * @var array
     */
    private $guessingOrder;

    /**
     * @var array
     */
    private $guessers;

    /**
     * @var array
     */
    private $preferredLocales;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * LocaleGuesserManager constructor.
     * @param array $guessingOrder
     * @param LoggerInterface|null $logger
     */
    public function __construct(array $guessingOrder, LoggerInterface $logger = null)
    {
        $this->guessingOrder = $guessingOrder;
        $this->guessers = [];
        $this->preferredLocales = [];
        $this->logger = $logger;
    }

    /**
     * Adds a guesser to this manager
     *
     * @param LocaleGuesserInterface $guesser The Guesser Service
     * @param string $alias Alias of the Service
     */
    public function addGuesser(LocaleGuesserInterface $guesser, $alias)
    {
        $this->guessers[$alias] = $guesser;
    }

    /**
     * Returns the guesser
     *
     * @param string $alias
     * @return LocaleGuesserInterface|null
     */
    public function getGuesser($alias)
    {
        if (array_key_exists($alias, $this->guessers)) {
            return $this->guessers[$alias];
        } else {
            return null;
        }
    }

    /**
     * Removes a guesser from this manager
     *
     * @param string $alias
     */
    public function removeGuesser($alias)
    {
        unset($this->guessers[$alias]);
    }

    /**
     * Loops through all the activated Locale Guessers and
     * calls the guessLocale methode and passing the current request.
     *
     * @param Request $request
     *
     * @throws InvalidConfigurationException
     *
     * @return boolean false if no locale is identified
     * @return bool the locale identified by the guessers
     */
    public function runLocaleGuessing(Request $request)
    {
        $this->preferredLocales = $request->getLanguages();
        foreach ($this->guessingOrder as $guesser) {
            if (null === $this->getGuesser($guesser)) {
                throw new InvalidConfigurationException(sprintf('Locale guesser service "%s" does not exist.', $guesser));
            }

            $guesserService = $this->getGuesser($guesser);
            $this->logEvent('Locale %s Guessing Service Loaded', ucfirst($guesser));
            if (false !== $guesserService->guessLocale($request)) {
                $locale = $guesserService->getIdentifiedLocale();
                $this->logEvent('Locale has been identified by guessing service: ( %s )', ucfirst($guesser));
                return $locale;
            }
            $this->logEvent('Locale has not been identified by the %s guessing service', ucfirst($guesser));
        }
        return false;
    }

    /**
     * Log detection events
     *
     * @param string $logMessage
     * @param string $parameters
     */
    private function logEvent($logMessage, $parameters = null)
    {
        if (null !== $this->logger) {
            $this->logger->debug(sprintf($logMessage, $parameters));
        }
    }

    /**
     * Retrieves the detected preferred locales
     *
     * @return array
     */
    public function getPreferredLocales()
    {
        return $this->preferredLocales;
    }

    /**
     * @return array
     */
    public function getGuessingOrder()
    {
        return $this->guessingOrder;
    }
}
