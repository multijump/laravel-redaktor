<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Concerns;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

trait InteractsWithRouting
{
    /**
     * Provides the Application instance.
     *
     * @return Application
     */
    abstract protected function getApplication(): Application;

    protected function getRouter(): Router
    {
        return $this->getApplication()->get('router');
    }

    /**
     * @param string $uri
     * @param array|string $methods
     * @param \Closure|array|null $action
     * @param string|array $middleware
     *
     * @return Route
     */
    protected function addRoute(string $uri, $methods = 'GET', $action = null, $middleware = 'api'): Route
    {
        /** @var Router $router */
        $router = $this->getApplication()->make('router');

        $route = $router->addRoute(
            $methods,
            $uri,
            $action ?? static function (): Response {
                return new Response();
            }
        );

        $middleware && $route->middleware($middleware);

        return $route;
    }
}