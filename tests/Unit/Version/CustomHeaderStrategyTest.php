<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit\Version;

use DSLabs\LaravelRedaktor\Version\CustomHeaderStrategy;
use DSLabs\LaravelRedaktor\Version\InvalidRequestException;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * @see CustomHeaderStrategy
 */
final class CustomHeaderStrategyTest extends TestCase
{
    public function testRetrievesEmptyVersionIfHeaderIsNotDefined(): void
    {
        // Act
        $version = (new CustomHeaderStrategy('Foo'))->resolve(new Request());

        //Assert
        self::assertSame('', (string)$version);
    }

    public function testRetrievesRevisionName(): void
    {
        // Arrange
        $request = new Request();
        $request->headers->set('Foo', 'bar');

        // Act
        $version = (new CustomHeaderStrategy('Foo'))->resolve($request);

        // Assert
        self::assertSame('bar', (string)$version);
    }

    public function testThrowsAnExceptionIfTheArgumentIsNotAnIlluminateRequest(): void
    {
        // Assert
        $this->expectException(InvalidRequestException::class);

        // Act
        (new CustomHeaderStrategy('Foo'))->resolve(
            new SymfonyRequest()
        );
    }
}
