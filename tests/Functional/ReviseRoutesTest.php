<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Functional;

use DSLabs\LaravelRedaktor\RedaktorServiceProvider;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithApplication;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithConfiguration;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithRouting;
use DSLabs\LaravelRedaktor\Tests\Request;
use DSLabs\Redaktor\Revision\RoutingRevision;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use PHPUnit\Framework\TestCase;


final class ReviseRoutesTest extends TestCase
{
    use InteractsWithApplication;
    use InteractsWithExceptionHandling;
    use InteractsWithRouting;
    use InteractsWithConfiguration;

    protected function getServiceProviders(): array
    {
        return [
            RedaktorServiceProvider::class,
        ];
    }

    public function testAddRoute(): void
    {
        // Arrange
        $revisedRoutes = tap(new RouteCollection(), static function (RouteCollection $routes) {
            $routes->add(
                new Route(
                    'GET',
                    '/foo',
                    static function (): Response {
                        return new Response();
                    }
                )
            );
        });
        $this->withConfig(
            'redaktor.revisions',
            [
                '2020-01' => [
                    self::createRoutingRevision($revisedRoutes),
                ],
            ]
        );

        // Act
        $okResponse = $this->getKernel()->handle(
            Request::createForVersion('2020-01', '/foo', 'GET')
        );
        $notFoundResponse = $this->getKernel()->handle(
            Request::create('/foo', 'GET')
        );

        // Arrange
        self::assertSame(200, $okResponse->getStatusCode());
        self::assertSame(404, $notFoundResponse->getStatusCode());
    }

    public function testRemoveRoute(): void
    {
        // Arrange
        $this->withConfig(
            'redaktor.revisions',
            [
                '2020-01' => [
                    self::createRoutingRevision(new RouteCollection()),
                ],
            ]
        );
        $this->addRoute('/foo', 'GET');

        // Act
        $okResponse = $this->getKernel()->handle(
            Request::create('/foo', 'GET')
        );
        $notFoundResponse = $this->getKernel()->handle(
            Request::createForVersion('2020-01', '/foo', 'GET')
        );

        // Arrange
        self::assertSame(200, $okResponse->getStatusCode());
        self::assertSame(404, $notFoundResponse->getStatusCode());
    }

    private static function createRoutingRevision(RouteCollection $routes): \Closure
    {
        return static function () use ($routes): RoutingRevision {

            return new class($routes) implements RoutingRevision {

                private $routes;

                public function __construct(RouteCollection $routes)
                {
                    $this->routes = $routes;
                }

                public function __invoke(iterable $routes): iterable
                {
                    return $this->routes;
                }
            };
        };
    }
}
