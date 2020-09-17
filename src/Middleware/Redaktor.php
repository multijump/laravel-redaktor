<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Middleware;

use Closure;
use DSLabs\LaravelRedaktor\Department\IlluminateDepartment;
use DSLabs\Redaktor\ChiefEditorInterface;
use DSLabs\Redaktor\Department\EditorDepartment;
use DSLabs\Redaktor\Editor\EditorInterface;
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
     * @var IlluminateDepartment
     */
    private $editorDepartment;

    /**
     * @var Router
     */
    private $router;

    public function __construct(
        ChiefEditorInterface $chiefEditor,
        EditorDepartment $editorDepartment,
        Router $router
    ) {
        $this->chiefEditor = $chiefEditor;
        $this->editorDepartment = $editorDepartment;
        $this->router = $router;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response|JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $editor = $this->chiefEditor
            ->speakTo($this->editorDepartment)
            ->appointEditor($request);

        self::reviseRoutes($editor, $this->router);

        $response = $next(
            $editor->reviseRequest()
        );

        return $editor->reviseResponse($response);
    }

    private static function reviseRoutes(EditorInterface $editor, Router $router): void
    {
        $routes = $router->getRoutes();
        $revisedRoutes = $editor->reviseRouting($routes);
        $router->setRoutes($revisedRoutes);
    }
}