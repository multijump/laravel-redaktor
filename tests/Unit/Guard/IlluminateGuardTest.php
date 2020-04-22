<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit;

use DSLabs\LaravelRedaktor\Guard\IlluminateGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

final class IlluminateGuardTest extends TestCase
{
    public function testDoesNothingIfParameterIsAnIlluminateRouteCollectionInstance(): void
    {
        // Assert
        $this->expectNotToPerformAssertions();

        // Act
        IlluminateGuard::assertRouteCollection(new RouteCollection());
    }

    /**
     * @dataProvider provideValueTypes
     */
    public function testGuardsAgainstNonIlluminateRouteCollectionInstance($value, string $type): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches(sprintf('[%s]', $type));

        // Act
        IlluminateGuard::assertRouteCollection($value);
    }

    public function testDoesNothingIfParameterIsAnIlluminateRequestInstance(): void
    {
        // Assert
        $this->expectNotToPerformAssertions();

        // Act
        IlluminateGuard::assertRequest(new Request());
    }

    public function testGuardsAgainstSymfonyRequestInstance(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(SymfonyRequest::class);

        // Act
        IlluminateGuard::assertRequest(new SymfonyRequest());
    }

    /**
     * @dataProvider provideValueTypes
     */
    public function testGuardsAgainstNonIlluminateRequestInstance($value, string $type): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($type);

        // Act
        IlluminateGuard::assertRequest($value);
    }

    public function provideValueTypes(): array
    {
        return [
            [null, 'NULL'],
            [true, 'bool'],
            [0, 'int'],
            [1.23, 'double'],
            ['foo', 'string'],
            [[], 'array'],
            [new \stdClass(), \stdClass::class],
            [static function () {}, 'Closure'],
            [new \ArrayIterator(), \ArrayIterator::class],
        ];
    }
}