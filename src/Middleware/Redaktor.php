<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Middleware;

use Closure;
use DSLabs\LaravelRedaktor\Department\IlluminateDepartment;
use DsLabs\LaravelRedaktor\IlluminateEditor;
use DSLabs\Redaktor\ChiefEditorInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;

final class Redaktor
{
    /**
     * @var ChiefEditorInterface
     */
    private $chiefEditor;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var IlluminateDepartment
     */
    private $illuminateDepartment;

    /**
     * @var Application
     */
    private $app;

    public function __construct(
        ChiefEditorInterface $chiefEditor,
        IlluminateDepartment $illuminateDepartment,
        Router $router,
        Application $app
    ) {
        $this->chiefEditor = $chiefEditor;
        $this->router = $router;
        $this->illuminateDepartment = $illuminateDepartment;
        $this->app = $app;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response|JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $editor = $this->chiefEditor
            ->speakTo($this->illuminateDepartment)
            ->appointEditor($request);

        self::reviseRoutes($editor, $this->router);

        $response = $next(
            $this->app->instance('request', $editor->reviseRequest())
        );

        return $editor->reviseResponse($response);
    }

    private static function reviseRoutes(IlluminateEditor $editor, Router $router): void
    {
        $routes = $router->getRoutes();
        $revisedRoutes = $editor->reviseRouting($routes);
        $router->setRoutes($revisedRoutes);
    }
}