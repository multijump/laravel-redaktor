<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Integration\Middleware;

use DSLabs\LaravelRedaktor\Middleware\Redaktor;
use DSLabs\LaravelRedaktor\RedaktorServiceProvider;
use DSLabs\LaravelRedaktor\Tests\Request;
use DSLabs\Redaktor\Revision\MessageRevision;
use DSLabs\Redaktor\Revision\RoutingRevision;
use Illuminate\Http\Response;
use Illuminate\Routing\RouteCollection;
use Orchestra\Testbench\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

final class RedaktorTest extends TestCase
{
    public function getPackageProviders($app)
    {
        return [
            RedaktorServiceProvider::class,
        ];
    }

    public function testNextClosureIsCalledWithOriginalRequestIfNoRevisionsAreRegistered(): void
    {
        // Arrange
        /** @var Redaktor $middleware */
        $middleware = $this->app->make(Redaktor::class);

        // Act
        $middleware->handle(
            $originalRequest = new Request(),
            static function (Request $request) use ($originalRequest): Response {
                // Assert
                self::assertSame($originalRequest, $request);

                return new Response();
            }
        );
    }

    public function testMiddlewareReturnsOriginalResponseIfNoRevisionsAreRegistered(): void
    {
        // Arrange
        /** @var Redaktor $middleware */
        $middleware = $this->app->make(Redaktor::class);
        $originalResponse = new Response();

        // Act
        $revisedResponse = $middleware->handle(
            new Request(),
            static function () use ($originalResponse): Response {
                return $originalResponse;
            }
        );

        // Assert
        self::assertSame($originalResponse, $revisedResponse);
    }

    public function testNextClosureIsCalledWithTheRevisedRequest(): void
    {
        // Arrange
        $revisionProphecy = $this->createMessageRevisionProphecy($revisedRequest = new Request());
        $this->app->get('config')->set(
            'redaktor.revisions',
            [
                'foo' => [
                    static function () use ($revisionProphecy) {
                        return $revisionProphecy->reveal();
                    }
                ],
            ]
        );

        /** @var Redaktor $middleware */
        $middleware = $this->app->make(Redaktor::class);

        // Act
        $middleware->handle(
            Request::forVersion('foo'),
            static function (Request $request) use ($revisedRequest): Response {
                // Assert
                self::assertSame($revisedRequest, $request);

                return new Response();
            }
        );
    }

    public function testApplyToResponseIsCalledWithTheOriginalResponse(): void
    {
        // Arrange
        $revisionProphecy = $this->createMessageRevisionProphecy(null, $revisedResponse = new Response());

        $this->app->get('config')->set(
            'redaktor.revisions',
            [
                'foo' => [
                    static function () use ($revisionProphecy) {
                        return $revisionProphecy->reveal();
                    }
                ],
            ]
        );

        /** @var Redaktor $middleware */
        $middleware = $this->app->make(Redaktor::class);

        // Act
        $originalResponse = new Response();
        $response = $middleware->handle(
            Request::forVersion('foo'),
            static function () use ($originalResponse): Response {
                return $originalResponse;
            }
        );

        // Assert
        $revisionProphecy->applyToResponse($originalResponse)->shouldHaveBeenCalled();
        self::assertSame($revisedResponse, $response);
    }

    public function testOriginalRoutesCollectionIsPassedInToTheRevision(): void
    {
        // Arrange
        $revisionProphecy = $this->createRoutingRevisionProphecy();

        $this->app->get('config')->set(
            'redaktor.revisions',
            [
                'foo' => [
                    static function () use ($revisionProphecy) {
                        return $revisionProphecy->reveal();
                    }
                ],
            ]
        );

        /** @var Redaktor $middleware */
        $middleware = $this->app->make(Redaktor::class);

        // Act
        $middleware->handle(
            Request::forVersion('foo'),
            self::createDummyMiddlewareClosure()
        );

        // Assert
        $originalRoutes = $this->app->get('routes');
        $revisionProphecy->__invoke($originalRoutes)->shouldHaveBeenCalled();
    }

    public function testRevisedRoutesCollectionIsUpdated(): void
    {
        // Arrange
        $revisionProphecy = $this->createRoutingRevisionProphecy($revisedRoutes = new RouteCollection());

        $this->app->get('config')->set(
            'redaktor.revisions',
            [
                'foo' => [
                    static function () use ($revisionProphecy) {
                        return $revisionProphecy->reveal();
                    }
                ],
            ]
        );

        /** @var Redaktor $middleware */
        $middleware = $this->app->make(Redaktor::class);

        // Act
        $middleware->handle(
            Request::forVersion('foo'),
            self::createDummyMiddlewareClosure()
        );

        // Assert
        self::assertSame($revisedRoutes, $this->app->get('routes'));
    }

    private function createMessageRevisionProphecy(
        Request $revisedRequest = null,
        Response $revisedResponse = null
    ): ObjectProphecy {
        $revision = $this->prophesize(MessageRevision::class);
        $revision->isApplicable(Argument::any())->willReturn(true);
        $revision->applyToRequest(Argument::any())->willReturn($revisedRequest ?? new Request());
        $revision->applyToResponse(Argument::any())->willReturn($revisedResponse ?? new Response());

        return $revision;
    }

    private function createRoutingRevisionProphecy(
        $revisedRoutesCollection = null
    ): ObjectProphecy {
        $revision = $this->prophesize(RoutingRevision::class);
        $revision->__invoke(Argument::any())->willReturn($revisedRoutesCollection ?? new RouteCollection());

        return $revision;
    }

    private static function createDummyMiddlewareClosure(): \Closure
    {
        return static function (Request $request): Response {
            return new Response();
        };
    }
}
