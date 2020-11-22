<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit\Version;

use DSLabs\LaravelRedaktor\Version\CustomHeaderResolver;
use DSLabs\LaravelRedaktor\Version\InvalidRequestException;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * @see CustomHeaderResolver
 */
final class CustomHeaderResolverTest extends TestCase
{
    public function testRetrievesEmptyVersionIfHeaderIsNotDefined(): void
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

    public function testThrowsAnExceptionIfTheArgumentIsNotAnIlluminateRequest(): void
    {
        // Assert
        $this->expectException(InvalidRequestException::class);

        // Act
        (new CustomHeaderResolver('Foo'))->resolve(
            new SymfonyRequest()
        );
    }
}
