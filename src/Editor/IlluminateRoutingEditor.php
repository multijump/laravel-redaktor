<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Editor;

use DSLabs\LaravelRedaktor\Guard\IlluminateGuard;
use DSLabs\Redaktor\Editor\RoutingEditorInterface;
use DSLabs\Redaktor\Version\Version;

final class IlluminateRoutingEditor implements RoutingEditorInterface
{
    /**
     * @var RoutingEditorInterface
     */
    private $juniorRoutingEditor;

    public function __construct(RoutingEditorInterface $juniorRoutingEditor)
    {
        $this->juniorRoutingEditor = $juniorRoutingEditor;
    }

    public function briefedVersion(): Version
    {
        return $this->juniorRoutingEditor->briefedVersion();
    }

    public function briefedRevisions(): array
    {
        return $this->juniorRoutingEditor->briefedRevisions();
    }

    public function reviseRouting(iterable $routes): iterable
    {
        IlluminateGuard::assertRouteCollection($routes);

        $revisedRoutes = $this->juniorRoutingEditor->reviseRouting($routes);

        IlluminateGuard::assertRouteCollection($revisedRoutes);

        return $revisedRoutes;
    }
}