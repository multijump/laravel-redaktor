<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Doubles;

use DSLabs\Redaktor\Revision\RoutingRevision;

final class RoutingRevisionStub implements RoutingRevision
{
    /**
     * @var iterable
     */
    private $revisedRoutes;

    public function __construct(
        iterable $revisedRoutes
    ) {
        $this->revisedRoutes = $revisedRoutes;
    }

    public function __invoke(iterable $routes): iterable
    {
        return $this->revisedRoutes;
    }
}