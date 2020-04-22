<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor;

use DSLabs\LaravelRedaktor\Guard\IlluminateGuard;
use DSLabs\Redaktor\EditorInterface;
use Illuminate\Http\Response;
use Illuminate\Routing\RouteCollection;

final class IlluminateEditor implements EditorInterface
{
    /**
     * @var EditorInterface
     */
    private $editor;

    public function __construct(EditorInterface $editor)
    {
        $this->editor = $editor;
    }

    /**
     * @param RouteCollection $routes
     *
     * @return RouteCollection
     */
    public function reviseRouting(iterable $routes): iterable
    {
        IlluminateGuard::assertRouteCollection($routes);

        $revisedRoutes = $this->editor->reviseRouting($routes);

        IlluminateGuard::assertRouteCollection($revisedRoutes);

        return $revisedRoutes;
    }

    /**
     * @inheritDoc
     *
     * @return Response
     */
    public function reviseRequest(): object
    {
        $revisedRequest = $this->editor->reviseRequest();

        IlluminateGuard::assertRequest($revisedRequest);

        return $revisedRequest;
    }

    /**
     * @param Response $response
     *
     * @return Response
     */
    public function reviseResponse(object $response): object
    {
        IlluminateGuard::assertResponse($response);

        $revisedResponse = $this->editor->reviseResponse($response);

        IlluminateGuard::assertResponse($revisedResponse);

        return $revisedResponse;
    }
}