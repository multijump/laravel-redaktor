<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Middleware;

use Closure;
use DSLabs\Redaktor\ChiefEditorInterface;
use DSLabs\Redaktor\Department\MessageDepartment;
use DSLabs\Redaktor\Version\VersionResolver;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class MessageRedaktor
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
     * @var MessageDepartment
     */
    private $messageDepartment;

    /**
     * @var Container
     */
    private $container;

    public function __construct(
        VersionResolver $versionResolver,
        ChiefEditorInterface $chiefEditor,
        MessageDepartment $messageDepartment,
        Container $container
    ) {
        $this->versionResolver = $versionResolver;
        $this->chiefEditor = $chiefEditor;
        $this->messageDepartment = $messageDepartment;
        $this->container = $container;
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

        $editor = $this->chiefEditor
            ->speakTo($this->messageDepartment)
            ->appointEditor($version);

        $response = $next(
            $this->container->instance('request', $editor->reviseRequest($request))
        );

        return $editor->reviseResponse($response);
    }
}