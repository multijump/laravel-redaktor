<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit\Version;

use DSLabs\LaravelRedaktor\Version\HeaderResolver;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

final class HeaderResolverTest extends TestCase
{
    public function testRetrievesNullIfHeaderIsNotDefined(): void
    {
        // Act
        $revision = (new HeaderResolver('Foo'))->resolve(new Request());

        //Assert
        self::assertNull($revision);
    }

    public function testRetrievesRevisionName(): void
    {
        // Arrange
        $request = new Request();
        $request->headers->set('Foo', 'bar');

        // Act
        $revision = (new HeaderResolver('Foo'))->resolve($request);

        // Assert
        self::assertSame('bar', $revision);
    }

    public function testThrowsAnInvalidateArgumentExceptionIfArgumentIsNotAnIlluminateRequest(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        (new HeaderResolver('Foo'))->resolve(
            new SymfonyRequest()
        );
    }
}