<?php

namespace NetBull\TranslationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use NetBull\TranslationBundle\Validator\MetaValidator;
use NetBull\TranslationBundle\Event\FilterLocaleSwitchEvent;

class LocaleController
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var MetaValidator
     */
    private $metaValidator;

    /**
     * @var RouterInterface|null
     */
    private $router;

    /**
     * @var bool
     */
    private $useReferrer;

    /**
     * @var string|null
     */
    private $redirectToRoute;

    /**
     * @var string
     */
    private $statusCode;

    /**
     * @param EventDispatcherInterface $dispatcher
     * @param MetaValidator $metaValidator
     * @param RouterInterface|null $router
     * @param bool $useReferrer
     * @param string|null $redirectToRoute
     * @param string $statusCode
     */
    public function __construct(EventDispatcherInterface $dispatcher, MetaValidator $metaValidator, RouterInterface $router = null, bool $useReferrer = true, string $redirectToRoute = null, string $statusCode = Response::HTTP_FOUND)
    {
        $this->dispatcher = $dispatcher;
        $this->metaValidator = $metaValidator;
        $this->router = $router;
        $this->useReferrer = $useReferrer;
        $this->redirectToRoute = $redirectToRoute;
        $this->statusCode = $statusCode;
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function switchAction(Request $request): RedirectResponse
    {
        $_locale = $request->attributes->get('_route_params')['_locale'] ?? $request->getLocale();
        $statusCode = (int)$request->attributes->get('statusCode', $this->statusCode);
        $useReferrer = $request->attributes->get('useReferrer', $this->useReferrer);
        $redirectToRoute = $request->attributes->get('route', $this->redirectToRoute);
        $metaValidator = $this->metaValidator;

        if (!$metaValidator->isAllowed($_locale)) {
            throw new NotFoundHttpException(sprintf('Not allowed to switch to locale %s', $_locale));
        }

        $localeSwitchEvent = new FilterLocaleSwitchEvent($request, $_locale);
        $this->dispatcher->dispatch($localeSwitchEvent);

        return $this->getResponse($request, $useReferrer, $statusCode, $redirectToRoute);
    }

    /**
     * @param Request $request
     * @param bool $useReferrer
     * @param int $statusCode
     * @param string|null $redirectToRoute
     * @return RedirectResponse
     */
    private function getResponse(Request $request, bool $useReferrer, int $statusCode, ?string $redirectToRoute): RedirectResponse
    {
        // Redirect the User
        if ($useReferrer && $request->headers->has('referer')) {
            $response = new RedirectResponse($request->headers->get('referer'), $statusCode);
        } elseif ($this->router && $redirectToRoute) {
            $target = $this->router->generate($redirectToRoute);
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
