<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Middleware;

use Closure;
use DSLabs\Redaktor\ChiefEditorInterface;
use DSLabs\Redaktor\Department\RoutingDepartment;
use DSLabs\Redaktor\Version\VersionResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;

final class RoutingRedaktor
{
    /**
     * @var VersionResolver
     */
    private $versionResolver;

    /**
     * @var ChiefEditorInterface
     */
    private $chiefEditor;

    /**
     * @var RoutingDepartment
     */
    private $routingDepartment;

    /**
     * @var Router
     */
    private $router;

    public function __construct(
        VersionResolver $versionResolver,
        ChiefEditorInterface $chiefEditor,
        RoutingDepartment $routingDepartment,
        Router $router
    ) {
        $this->versionResolver = $versionResolver;
        $this->chiefEditor = $chiefEditor;
        $this->routingDepartment = $routingDepartment;
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
        $version = $this->versionResolver->resolve($request);

        /** @var RouteCollection $routes */
        $routes = $this->chiefEditor
            ->speakTo($this->routingDepartment)
            ->appointEditor($version)
            ->reviseRouting(
                $originalRoutes = $this->router->getRoutes()
            );

        $routes !== $originalRoutes && $this->router->setRoutes($routes);

        $response = $next($request);

        $routes !== $originalRoutes && $this->router->setRoutes($originalRoutes);

        return $response;
    }
}