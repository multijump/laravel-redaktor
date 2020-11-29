<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Integration\Middleware;

use DSLabs\LaravelRedaktor\Middleware\MessageRedaktor;
use DSLabs\LaravelRedaktor\RedaktorServiceProvider;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithApplication;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithConfiguration;
use DSLabs\LaravelRedaktor\Tests\Doubles\MessageRevisionStub;
use DSLabs\LaravelRedaktor\Tests\Request;
use DSLabs\Redaktor\Revision\MessageRevision;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;

/**
 * @see MessageRedaktor
 */
final class MessageRedaktorTest extends TestCase
{
    use InteractsWithApplication;
    use InteractsWithConfiguration;

    protected function getServiceProviders(Application $app): array
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
        $this->withConfig([
            'redaktor.revisions' => [
                'foo' => [
                    self::createMessageRevisionDefinition($revisedRequest = new Request()),
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

    public function testRevisesResponseReturnedByNextClosure(): void
    {
        // Arrange
        $this->withConfig([
            'redaktor.revisions' => [
                'foo' => [
                    self::createMessageRevisionDefinition(null, $revisedResponse = new Response()),
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
        self::assertSame($revisedResponse, $response);
    }

    public function testSupportsJsonResponse(): void
    {
        // Arrange
        $this->withConfig([
            'redaktor.revisions' => [
                'foo' => [
                    self::createMessageRevisionDefinition(null, $revisedJsonResponse = new JsonResponse()),
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

    private static function createMessageRevisionDefinition(
        Request $revisedRequest = null,
        $revisedResponse = null
    ): \Closure {
        return static function () use ($revisedRequest, $revisedResponse): MessageRevision {
            return new MessageRevisionStub(
                $revisedRequest ?? new Request(),
                $revisedResponse ?? new Response(),
                true
            );
        };
    }

    private static function createDummyMiddlewareClosure(): \Closure
    {
        return static function (Request $request): Response {
            return new Response();
        };
    }
}
