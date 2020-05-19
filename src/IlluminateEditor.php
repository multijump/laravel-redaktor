<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor;

use DSLabs\LaravelRedaktor\Guard\IlluminateGuard;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Revision\Revision;
use Illuminate\Http\Request;
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
     * @inheritDoc
     *
     * @return Request
     */
    public function retrieveBriefedRequest(): object
    {
        return $this->editor->retrieveBriefedRequest();
    }

    /**
     * @inheritDoc
     *
     * @return Revision[]
     */
    public function retrieveBriefedRevisions(): array
    {
        return $this->editor->retrieveBriefedRevisions();
    }

    /**
     * @inheritDoc
     *
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
     * @inheritDoc
     *
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