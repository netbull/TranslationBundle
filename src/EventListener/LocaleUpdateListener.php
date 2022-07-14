<?php

namespace NetBull\TranslationBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use NetBull\TranslationBundle\Locale\Cookie\LocaleCookie;
use NetBull\TranslationBundle\Locale\Session\LocaleSession;
use NetBull\TranslationBundle\Event\FilterLocaleSwitchEvent;

class LocaleUpdateListener implements EventSubscriberInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * @var LocaleCookie
     */
    private LocaleCookie $localeCookie;

    /**
     * @var LocaleSession
     */
    private LocaleSession $localeSession;

    /**
     * @var array
     */
    private array $registeredGuessers;

    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger;

    /**
     * @var string|null
     */
    private ?string $locale = null;

    /**
     * @param EventDispatcherInterface $dispatcher
     * @param LocaleCookie $localeCookie
     * @param LocaleSession $localeSession
     * @param array $registeredGuessers
     * @param LoggerInterface|null $logger
     */
    public function __construct(EventDispatcherInterface $dispatcher, LocaleCookie $localeCookie, LocaleSession $localeSession, array $registeredGuessers = [], LoggerInterface $logger = null)
    {
        $this->dispatcher = $dispatcher;
        $this->localeCookie = $localeCookie;
        $this->localeSession = $localeSession;
        $this->registeredGuessers = $registeredGuessers;
        $this->logger = $logger;
    }

    /**
     * Processes the locale updates. Adds listener for the cookie and updates the session.
     * @param FilterLocaleSwitchEvent $event
     */
    public function onLocaleChange(FilterLocaleSwitchEvent $event)
    {
        $this->locale = $event->getLocale();
        $this->updateCookie($event->getRequest(), $this->localeCookie->setCookieOnChange());
        $this->updateSession();
    }

    /**
     * @param Request $request
     * @param $update
     * @return bool
     */
    public function updateCookie(Request $request, $update): bool
    {
        if ($this->checkGuesser('cookie')
            && $update === true
            && $request->cookies->get($this->localeCookie->getName()) !== $this->locale
        ) {
            $this->dispatcher->addListener(KernelEvents::RESPONSE, [$this, 'updateCookieOnResponse']);
            return true;
        }

        return false;
    }

    /**
     * @param ResponseEvent $event
     * @return Response
     */
    public function updateCookieOnResponse(ResponseEvent $event): Response
    {
        $response = $event->getResponse();
        $cookie = $this->localeCookie->getLocaleCookie($this->locale);
        $response->headers->setCookie($cookie);
        if (null !== $this->logger) {
            $this->logger->info(sprintf('Locale Cookie set to [ %s ]', $this->locale));
        }

        return $response;
    }

    /**
     * @return bool
     */
    public function updateSession(): bool
    {
        if ($this->checkGuesser('session') && $this->localeSession->hasLocaleChanged($this->locale)) {
            if (null !== $this->logger) {
                $this->logger->info(sprintf('Session var "%s" set to [ %s ]', $this->localeSession->getSessionVar(), $this->locale));
            }
            $this->localeSession->setLocale($this->locale);
            return true;
        }

        return false;
    }

    /**
     * @param string $guesser
     * @return bool
     */
    private function checkGuesser(string $guesser): bool
    {
        return in_array($guesser, $this->registeredGuessers);
    }

    /**
     * @return string[][]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered after the Router to have access to the _locale and before the Symfony LocaleListener
            FilterLocaleSwitchEvent::class => ['onLocaleChange'],
        ];
    }
}
