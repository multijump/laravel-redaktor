<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit;

use DSLabs\LaravelRedaktor\Guard\IlluminateGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\RouteCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * @see IlluminateGuard
 */
final class IlluminateGuardTest extends TestCase
{
    public function testAssertRouteCollectionIsAnIlluminateRouteCollectionInstance(): void
    {
        // Assert
        $this->expectNotToPerformAssertions();

        // Act
        IlluminateGuard::assertRouteCollection(new RouteCollection());
    }

    /**
     * @dataProvider provideInvalidRouteCollections
     */
    public function testGuardsRouteCollectionAgainstNonIlluminateRouteCollectionInstance($routeCollection, string $type): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches(sprintf('[%s]', $type));

        // Act
        IlluminateGuard::assertRouteCollection($routeCollection);
    }

    public function testAssertRequestIsAnIlluminateRequestInstance(): void
    {
        // Assert
        $this->expectNotToPerformAssertions();

        // Act
        IlluminateGuard::assertRequest(new Request());
    }

    /**
     * @dataProvider provideInvalidRequests
     */
    public function testGuardsRequestAgainstNonIlluminateRequestInstance($request, string $type): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($type);

        // Act
        IlluminateGuard::assertRequest($request);
    }

    public function testAssertResponseIsAnIlluminateResponseInstance(): void
    {
        // Assert
        $this->expectNotToPerformAssertions();

        // Act
        IlluminateGuard::assertResponse(new Response());
    }

    public function testAssertResponseIsAnIlluminateJsonResponseInstance(): void
    {
        // Assert
        $this->expectNotToPerformAssertions();

        // Act
        IlluminateGuard::assertResponse(new JsonResponse());
    }

    /**
     * @dataProvider provideInvalidResponses
     */
    public function testGuardsResponseAgainstNonIlluminateResponseInstance($response, string $type): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($type);

        // Act
        IlluminateGuard::assertResponse($response);
    }

    public function provideInvalidRouteCollections(): array
    {
        return $this->provideInvalidArguments() +
            [
                [new \ArrayIterator(), \ArrayIterator::class]
            ];

    }

    public function provideInvalidRequests(): array
    {
        return $this->provideInvalidArguments() +
            [
                SymfonyRequest::class => [new SymfonyRequest(), SymfonyRequest::class],
            ];
    }

    public function provideInvalidResponses(): array
    {
        return $this->provideInvalidArguments() +
            [
                SymfonyResponse::class => [new SymfonyResponse(), SymfonyResponse::class]
            ];
    }

    private function provideInvalidArguments(): array
    {
        return [
            'null' => [null, 'NULL'],
            'boolean' => [true, 'bool'],
            'integer' => [0, 'int'],
            'double' => [1.23, 'double'],
            'string' => ['foo', 'string'],
            'array' => [[], 'array'],
            \stdClass::class => [new \stdClass(), \stdClass::class],
            'Closure' => [static function () { }, 'Closure'],
        ];
    }
}