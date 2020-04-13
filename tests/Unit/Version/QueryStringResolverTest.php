<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit\Version;

use DSLabs\LaravelRedaktor\Version\QueryStringResolver;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

final class QueryStringResolverTest extends TestCase
{
    public function testRetrievesNullIfParameterIsNotDefined(): void
    {
        // Act
        $revision = (new QueryStringResolver('foo'))->resolve(new Request());

        //Assert
        self::assertNull($revision);
    }

    public function testRetrievesRevisionName(): void
    {
        // Arrange
        $request = new Request(['foo' => 'bar']);

        // Act
        $revision = (new QueryStringResolver('foo'))->resolve($request);

        // Assert
        self::assertSame('bar', $revision);
    }

    public function testThrowsAnInvalidateArgumentExceptionIfArgumentIsNotAnIlluminateRequest(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        (new QueryStringResolver('Foo'))->resolve(
            new SymfonyRequest()
        );
    }
}