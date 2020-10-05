<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Functional;

use DSLabs\LaravelRedaktor\RedaktorServiceProvider;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithApplication;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithConfiguration;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithRouting;
use DSLabs\LaravelRedaktor\Tests\Request;
use DSLabs\Redaktor\Revision\ResponseRevision;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;

final class ReviseResponseTest extends TestCase
{
    use InteractsWithApplication;
    use InteractsWithRouting;
    use InteractsWithConfiguration;

    public function getServiceProviders(): array
    {
        return [
            RedaktorServiceProvider::class,
        ];
    }

    public function testResponseWithNoRevisionsRegistered(): void
    {
        // Arrange
        $originalResponse = new Response();
        $this->addRoute(
            '/foo',
            'GET',
            static function () use ($originalResponse): object {
                return $originalResponse;
            }
        );

        // Act
        $response = $this->getKernel()->handle(
            Request::create('/foo')
        );

        // Assert
        self::assertSame($originalResponse, $response);
    }

    public function testRevisedResponseIsReturnedBack(): void
    {
        // Arrange
        $this->withConfig(
            'redaktor.revisions',
            [
                '2020-01' => [
                    self::createResponseRevision($revisedResponse = new Response),
                ],
            ]
        );
        $this->addRoute(
            '/foo',
            'GET'
        );

        // Act
        $response = $this->getKernel()->handle(
            Request::createForVersion('2020-01', '/foo')
        );

        // Assert
        self::assertSame($revisedResponse, $response);
    }

    public function testOriginalResponseIsReturnedIfNoVersionIsSpecified(): void
    {
        // Arrange
        $this->withConfig(
            'redaktor.revisions',
            [
                '2020-01' => [
                    self::createResponseRevision($revisedResponse = new Response),
                ],
            ]
        );

        $originalResponse = new Response();
        $this->addRoute(
            '/foo',
            'GET',
            static function () use ($originalResponse): object {
                return $originalResponse;
            }
        );

        // Act
        $response = $this->getKernel()->handle(
            Request::create('/foo')
        );

        // Assert
        self::assertSame($originalResponse, $response);
    }

    private static function createResponseRevision(Response $response): \Closure
    {
        return static function () use ($response): ResponseRevision {

            return new class($response) implements ResponseRevision {

                private $response;

                public function __construct(Response $response)
                {
                    $this->response = $response;
                }

                public function isApplicable(object $request): bool
                {
                    return true;
                }

                public function applyToResponse(object $response): object
                {
                    return $this->response;
                }
            };
        };
    }
}
