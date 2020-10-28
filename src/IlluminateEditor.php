<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor;

use DSLabs\LaravelRedaktor\Guard\IlluminateGuard;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Version\Version;
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
     */
    public function briefedVersion(): Version
    {
        return $this->editor->briefedVersion();
    }

    /**
     * @inheritDoc
     *
     * @return Revision[]
     */
    public function briefedRevisions(): array
    {
        return $this->editor->briefedRevisions();
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
     * @return Request
     */
    public function reviseRequest(object $request): object
    {
        IlluminateGuard::assertRequest($request);

        $revisedRequest = $this->editor->reviseRequest($request);

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