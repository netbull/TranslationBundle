<?php

namespace NetBull\TranslationBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use NetBull\TranslationBundle\Guessers\LocaleGuesserManager;
use NetBull\TranslationBundle\Event\FilterLocaleSwitchEvent;
use NetBull\TranslationBundle\Matcher\BestLocaleMatcherInterface;

/**
 * Class LocaleListener
 * @package NetBull\TranslationBundle\EventListener
 */
class LocaleListener implements EventSubscriberInterface
{
    /**
     * @var string Default framework locale
     */
    private $defaultLocale;

    /**
     * @var LocaleGuesserManager
     */
    private $guesserManager;

    /**
     * @var BestLocaleMatcherInterface
     */
    private $bestLocaleMatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var boolean
     */
    private $disableVaryHeader = false;

    /**
     * @var string
     */
    private $excludedPattern;

    /**
     * LocaleListener constructor.
     * @param LocaleGuesserManager $guesserManager
     * @param string $defaultLocale
     * @param BestLocaleMatcherInterface|null $bestLocaleMatcher
     * @param LoggerInterface|null $logger
     */
    public function __construct(LocaleGuesserManager $guesserManager, $defaultLocale = 'en', BestLocaleMatcherInterface $bestLocaleMatcher = null, LoggerInterface $logger = null)
    {
        $this->defaultLocale = $defaultLocale;
        $this->guesserManager = $guesserManager;
        $this->bestLocaleMatcher = $bestLocaleMatcher;
        $this->logger = $logger;
    }

    /**
     * Called at the "kernel.request" event
     *
     * Call the LocaleGuesserManager to guess the locale
     * by the activated guessers
     *
     * Sets the identified locale as default locale to the request
     *
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if ($this->excludedPattern && preg_match(sprintf('#%s#', $this->excludedPattern), $request->getPathInfo())) {
            return;
        }

        $request->setDefaultLocale($this->defaultLocale);
        $manager = $this->guesserManager;
        $locale = $manager->runLocaleGuessing($request);
        if ($locale && $this->bestLocaleMatcher) {
            $locale = $this->bestLocaleMatcher->match($locale);
        }

        if ($locale) {
            $this->logEvent('Setting [ %s ] as locale for the (Sub-)Request', $locale);
            $request->setLocale($locale);
            $request->attributes->set('_locale', $locale);

            if (
                ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST || $request->isXmlHttpRequest())
                && ($manager->getGuesser('session') || $manager->getGuesser('cookie'))
            ) {
                $localeSwitchEvent = new FilterLocaleSwitchEvent($request, $locale);
                $this->dispatcher->dispatch($localeSwitchEvent, FilterLocaleSwitchEvent::NAME);
            }
        }
    }

    /**
     * This Listener adds a vary header to all responses.
     *
     * @param ResponseEvent $event
     *
     * @return Response
     */
    public function onLocaleDetectedSetVaryHeader(ResponseEvent $event): Response
    {
        $response = $event->getResponse();
        if (!$this->disableVaryHeader) {
            $response->setVary('Accept-Language', false);
        }

        return $response;
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param bool $disableVaryHeader
     */
    public function setDisableVaryHeader(bool $disableVaryHeader)
    {
        $this->disableVaryHeader = $disableVaryHeader;
    }

    /**
     * @param string|null $excludedPattern
     */
    public function setExcludedPattern(?string $excludedPattern)
    {
        $this->excludedPattern = $excludedPattern;
    }

    /**
     * Log detection events
     *
     * @param string $logMessage
     * @param mixed $parameters
     */
    private function logEvent(string $logMessage, $parameters = null)
    {
        if (null !== $this->logger) {
            $this->logger->info(sprintf($logMessage, $parameters));
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered after the Router to have access to the _locale and before the Symfony LocaleListener
            KernelEvents::REQUEST => [['onKernelRequest', 25]],
            KernelEvents::RESPONSE => ['onLocaleDetectedSetVaryHeader']
        ];
    }
}
