<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Functional;

use DSLabs\LaravelRedaktor\RedaktorServiceProvider;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithApplication;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithConfiguration;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithRouting;
use DSLabs\LaravelRedaktor\Tests\Doubles\RequestRevisionStub;
use DSLabs\LaravelRedaktor\Tests\Request;
use DSLabs\Redaktor\Revision\RequestRevision;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Http\Request as IlluminateRequest;
use PHPUnit\Framework\TestCase;

final class ReviseRequestTest extends TestCase
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

    public function testApiRequestLifeCycleWithNoRevisionsRegistered(): void
    {
        // Arrange
        $this->withoutExceptionHandling();
        $originalRequest = Request::create('/foo');
        $this->addRoute(
            '/foo',
            'GET',
            static function (IlluminateRequest $request) use ($originalRequest): void {
                // Assert
                self::assertSame($originalRequest, $request);
            }
        );

        // Act
        $this->getKernel()->handle(
            $originalRequest
        );
    }

    public function testRevisedRequestIsInjectedInTheController(): void
    {
        // Arrange
        $this->withoutExceptionHandling();
        $this->withConfig(
            'redaktor.revisions',
            [
                '2020-01' => [
                    self::createRequestRevisionDefinition($revisedRequest = Request::create('/foo')),
                ],
            ]
        );

        $this->addRoute(
            '/foo',
            'GET',
            static function (IlluminateRequest $request) use ($revisedRequest): void {
                // Assert
                self::assertSame($revisedRequest, $request);
            }
        );

        // Act
        $this->getKernel()->handle(
            Request::createForVersion('2020-01', '/foo')
        );
    }

    public function testOriginalRequestIsInjectedInTheControllerIfNoVersionIsSpecified(): void
    {
        // Arrange
        $this->withoutExceptionHandling();
        $this->withConfig(
            'redaktor.revisions',
            [
                '2020-01' => [
                    self::createRequestRevisionDefinition(Request::create('/baz')),
                ],
            ]
        );
        $originalRequest = Request::create('/foo');

        $this->addRoute(
            '/foo',
            'GET',
            static function (IlluminateRequest $request) use ($originalRequest): void {
                // Assert
                self::assertSame($originalRequest, $request);
            }
        );

        // Act
        $this->getKernel()->handle(
            $originalRequest
        );
    }

    private static function createRequestRevisionDefinition(Request $revisedRequest): \Closure
    {
        return static function () use ($revisedRequest): RequestRevision {
            return new RequestRevisionStub($revisedRequest, true);
        };
    }
}
