<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Integration\Middleware;

use DSLabs\LaravelRedaktor\Middleware\MessageRedaktor;
use DSLabs\LaravelRedaktor\RedaktorServiceProvider;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithApplication;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithConfiguration;
use DSLabs\LaravelRedaktor\Tests\Request;
use DSLabs\Redaktor\Revision\MessageRevision;
use DSLabs\Redaktor\Revision\RoutingRevision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\RouteCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @see MessageRedaktor
 */
final class MessageRedaktorTest extends TestCase
{
    use InteractsWithApplication;
    use InteractsWithConfiguration;

    protected function getServiceProviders(): array
    {
        return [
            RedaktorServiceProvider::class,
        ];
    }

    public function testNextClosureIsCalledWithOriginalRequestIfNoRevisionsAreRegistered(): void
    {
        // Arrange
        /** @var MessageRedaktor $middleware */
        $middleware = $this->app->make(MessageRedaktor::class);

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
        /** @var MessageRedaktor $middleware */
        $middleware = $this->app->make(MessageRedaktor::class);
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
        $this->withConfig([
            'redaktor.revisions' => [
                'foo' => [
                    static function () use ($revisionProphecy): object {
                        return $revisionProphecy->reveal();
                    }
                ],
            ],
        ]);

        /** @var MessageRedaktor $middleware */
        $middleware = $this->app->make(MessageRedaktor::class);

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
        $revisionProphecy = $this->createMessageRevisionProphecy(
            $revisedRequest = new Request(),
            $revisedResponse = new Response()
        );
        $this->withConfig([
            'redaktor.revisions' => [
                'foo' => [
                    static function () use ($revisionProphecy): object {
                        return $revisionProphecy->reveal();
                    }
                ],
            ],
        ]);

        /** @var MessageRedaktor $middleware */
        $middleware = $this->app->make(MessageRedaktor::class);

        // Act
        $originalResponse = new Response();
        $response = $middleware->handle(
            Request::forVersion('foo'),
            static function () use ($originalResponse): Response {
                return $originalResponse;
            }
        );

        // Assert
        $revisionProphecy->applyToResponse($originalResponse, $revisedRequest)->shouldHaveBeenCalled();
        self::assertSame($revisedResponse, $response);
    }

    public function testSupportsJsonResponse(): void
    {
        // Arrange
        $revisionProphecy = $this->createMessageRevisionProphecy(null, $revisedJsonResponse = new JsonResponse());

        $this->withConfig([
            'redaktor.revisions' => [
                'foo' => [
                    static function () use ($revisionProphecy): object {
                        return $revisionProphecy->reveal();
                    }
                ],
            ],
        ]);

        /** @var MessageRedaktor $middleware */
        $middleware = $this->app->make(MessageRedaktor::class);

        // Act
        $response = $middleware->handle(
            Request::forVersion('foo'),
            self::createDummyMiddlewareClosure()
        );

        // Assert
        self::assertSame($revisedJsonResponse, $response);
    }

    private function createMessageRevisionProphecy(
        Request $revisedRequest = null,
        $revisedResponse = null
    ): ObjectProphecy {
        $revision = $this->prophesize(MessageRevision::class);
        $revision->isApplicable(Argument::any())->willReturn(true);
        $revision->applyToRequest(Argument::any())->willReturn($revisedRequest ?? new Request());
        $revision->applyToResponse(Argument::cetera())->willReturn($revisedResponse ?? new Response());

        return $revision;
    }

    private static function createDummyMiddlewareClosure(): \Closure
    {
        return static function (Request $request): Response {
            return new Response();
        };
    }
}
