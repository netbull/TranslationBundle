<?php

namespace NetBull\TranslationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use NetBull\TranslationBundle\Locale\Events;
use NetBull\TranslationBundle\Event\FilterLocaleSwitchEvent;
use NetBull\TranslationBundle\Validator\MetaValidator;

/**
 * Class LocaleController
 * @package NetBull\TranslationBundle\Controller
 */
class LocaleController
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var null|RouterInterface
     */
    private $router;

    /**
     * @var MetaValidator
     */
    private $metaValidator;

    /**
     * @var bool
     */
    private $useReferrer;

    /**
     * @var null
     */
    private $redirectToRoute;

    /**
     * @var string
     */
    private $statusCode;

    /**
     * LocaleController constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param MetaValidator $metaValidator
     * @param RouterInterface|null $router
     * @param bool $useReferrer
     * @param null $redirectToRoute
     * @param string $statusCode
     */
    public function __construct(EventDispatcherInterface $dispatcher, MetaValidator $metaValidator, RouterInterface $router = null, $useReferrer = true, $redirectToRoute = null, $statusCode = '302')
    {
        $this->dispatcher = $dispatcher;
        $this->router = $router;
        $this->metaValidator = $metaValidator;
        $this->useReferrer = $useReferrer;
        $this->redirectToRoute = $redirectToRoute;
        $this->statusCode = $statusCode;
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function switchAction(Request $request)
    {
        $_locale = $request->attributes->get('_route_params')['_locale'] ?? $request->getLocale();
        $statusCode = $request->attributes->get('statusCode', $this->statusCode);
        $useReferrer = $request->attributes->get('useReferrer', $this->useReferrer);
        $redirectToRoute = $request->attributes->get('route', $this->redirectToRoute);
        $metaValidator = $this->metaValidator;

        if (!$metaValidator->isAllowed($_locale)) {
            throw new \InvalidArgumentException(sprintf('Not allowed to switch to locale %s', $_locale));
        }

        $localeSwitchEvent = new FilterLocaleSwitchEvent($request, $_locale);
        $this->dispatcher->dispatch(Events::ON_LOCALE_CHANGE, $localeSwitchEvent);

        return $this->getResponse($request, $useReferrer, $statusCode, $redirectToRoute, $_locale);
    }

    /**
     * @param Request $request
     * @param $useReferrer
     * @param $statusCode
     * @param $redirectToRoute
     * @param $_locale
     * @return RedirectResponse
     */
    private function getResponse(Request $request, $useReferrer, $statusCode, $redirectToRoute, $_locale)
    {
        // Redirect the User
        if ($useReferrer && $request->headers->has('referer')) {
            $response = new RedirectResponse($request->headers->get('referer'), $statusCode);
        } elseif ($this->router && $redirectToRoute) {
            $target = $this->router->generate($redirectToRoute, ['_locale' => $_locale]);
            if ($request->getQueryString()) {
                if (!strpos($target, '?')) {
                    $target .= '?';
                }
                $target .= $request->getQueryString();
            }
            $response = new RedirectResponse($target, $statusCode);
        } else {
            $response = new RedirectResponse($request->getScheme() . '://' . $request->getHttpHost() . '/', $statusCode);
        }

        return $response;
    }
}
