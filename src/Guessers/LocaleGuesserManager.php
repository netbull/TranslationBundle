<?php

namespace NetBull\TranslationBundle\Guessers;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class LocaleGuesserManager
{
    /**
     * @var array
     */
    private array $guessingOrder;

    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger;

    /**
     * @var array
     */
    private array $guessers = [];

    /**
     * @var array
     */
    private array $preferredLocales = [];

    /**
     * @param array $guessingOrder
     * @param LoggerInterface|null $logger
     */
    public function __construct(array $guessingOrder, LoggerInterface $logger = null)
    {
        $this->guessingOrder = $guessingOrder;
        $this->logger = $logger;
    }

    /**
     * @param LocaleGuesserInterface $guesser The Guesser Service
     * @param string $alias Alias of the Service
     */
    public function addGuesser(LocaleGuesserInterface $guesser, string $alias)
    {
        $this->guessers[$alias] = $guesser;
    }

    /**
     * @param string $alias
     * @return LocaleGuesserInterface|null
     */
    public function getGuesser(string $alias): ?LocaleGuesserInterface
    {
        if (array_key_exists($alias, $this->guessers)) {
            return $this->guessers[$alias];
        } else {
            return null;
        }
    }

    /**
     * @param string $alias
     */
    public function removeGuesser(string $alias)
    {
        unset($this->guessers[$alias]);
    }

    /**
     * @param Request $request
     * @return string|null
     */
    public function runLocaleGuessing(Request $request): ?string
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
        return null;
    }

    /**
     * Log detection events
     * @param string $logMessage
     * @param mixed $parameters
     */
    private function logEvent(string $logMessage, $parameters = null)
    {
        if (null !== $this->logger) {
            $this->logger->debug(sprintf($logMessage, $parameters));
        }
    }

    /**
     * Retrieves the detected preferred locales
     * @return array
     */
    public function getPreferredLocales(): array
    {
        return $this->preferredLocales;
    }

    /**
     * @return array
     */
    public function getGuessingOrder(): array
    {
        return $this->guessingOrder;
    }
}
