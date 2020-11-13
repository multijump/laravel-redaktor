<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit\Version;

use DSLabs\LaravelRedaktor\Version\CustomHeaderResolver;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * @see CustomHeaderResolver
 */
final class CustomHeaderResolverTest extends TestCase
{
    public function testRetrievesNullIfHeaderIsNotDefined(): void
    {
        // Act
        $version = (new CustomHeaderResolver('Foo'))->resolve(new Request());

        //Assert
        self::assertSame('', (string)$version);
    }

    public function testRetrievesRevisionName(): void
    {
        // Arrange
        $request = new Request();
        $request->headers->set('Foo', 'bar');

        // Act
        $version = (new CustomHeaderResolver('Foo'))->resolve($request);

        // Assert
        self::assertSame('bar', (string)$version);
    }

    public function testThrowsAnInvalidateArgumentExceptionIfArgumentIsNotAnIlluminateRequest(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        (new CustomHeaderResolver('Foo'))->resolve(
            new SymfonyRequest()
        );
    }
}
