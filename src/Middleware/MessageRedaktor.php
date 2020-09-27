<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Middleware;

use Closure;
use DSLabs\Redaktor\ChiefEditorInterface;
use DSLabs\Redaktor\Department\EditorDepartment;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class MessageRedaktor
{
    /**
     * @var ChiefEditorInterface
     */
    private $chiefEditor;

    /**
     * @var EditorDepartment
     */
    private $editorDepartment;

    /**
     * @var Container
     */
    private $container;

    public function __construct(
        ChiefEditorInterface $chiefEditor,
        EditorDepartment $editorDepartment,
        Container $container
    ) {
        $this->chiefEditor = $chiefEditor;
        $this->editorDepartment = $editorDepartment;
        $this->container = $container;
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

        $response = $next(
            $this->container->instance('request', $editor->reviseRequest())
        );

        return $editor->reviseResponse($response);
    }
}