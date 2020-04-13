<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Middleware;

use Closure;
use DSLabs\Redaktor\ChiefEditorInterface;
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

    public function __construct(
        ChiefEditorInterface $chiefEditor,
        Router $router
    ) {
        $this->chiefEditor = $chiefEditor;
        $this->router = $router;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $editor = $this->chiefEditor->appointEditor($request);

        $routes = $this->router->getRoutes();
        $this->router->setRoutes(
            $editor->reviseRouting($routes)
        );

        $response = $next(
            $editor->reviseRequest()
        );

        return $editor->reviseResponse($response);
    }
}