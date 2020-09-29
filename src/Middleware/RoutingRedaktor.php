<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Middleware;

use Closure;
use DSLabs\Redaktor\ChiefEditorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;

final class RoutingRedaktor
{
    /**
     * @var ChiefEditorInterface
     */
    private $chiefEditor;

    /**
     * @var Router
     */
    private $router;

    public function __construct(
        ChiefEditorInterface $chiefEditor,
        Router $router
    )
    {
        $this->chiefEditor = $chiefEditor;
        $this->router = $router;
    }

    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return Response|JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var RouteCollection $routes */
        $routes = $this->chiefEditor
            ->appointEditor($request)
            ->reviseRouting(
                $originalRoutes = $this->router->getRoutes()
            );

        $routes !== $originalRoutes && $this->router->setRoutes($routes);

        $response = $next($request);

        $routes !== $originalRoutes && $this->router->setRoutes($originalRoutes);

        return $response;
    }
}