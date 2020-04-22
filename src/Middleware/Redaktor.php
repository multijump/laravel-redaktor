<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Middleware;

use Closure;
use DSLabs\LaravelRedaktor\IlluminateChiefEditor;
use DsLabs\LaravelRedaktor\IlluminateEditor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;

final class Redaktor
{
    /**
     * @var IlluminateChiefEditor
     */
    private $chiefEditor;

    /**
     * @var Router
     */
    private $router;

    public function __construct(
        IlluminateChiefEditor $chiefEditor,
        Router $router
    ) {
        $this->chiefEditor = $chiefEditor;
        $this->router = $router;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $editor = $this->chiefEditor->appointEditor($request);

        self::reviseRoutes($editor, $this->router);

        $response = $next(
            $editor->reviseRequest()
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