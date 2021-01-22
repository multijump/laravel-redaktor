<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit\Version;

use DSLabs\LaravelRedaktor\Version\InvalidRequestException;
use DSLabs\LaravelRedaktor\Version\UriPathStrategy;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * @see UriPathStrategy
 */
final class UriPathStrategyTest extends TestCase
{
    public function testRetrievesEmptyVersionIfVersionIsNotDefined(): void
    {
        // Act
        $request = Request::create('/');
        $version = (new UriPathStrategy(0))->resolve($request);

        //Assert
        self::assertSame('', (string)$version);
    }

    public function testRetrievesRevisionName(): void
    {
        // Arrange
        $request = Request::create('/v1/foo/bar');

        // Act
        $version = (new UriPathStrategy(0))->resolve($request);

        // Assert
        self::assertSame('v1', (string)$version);
    }

    public function testThrowsAnExceptionIfTheArgumentIsNotAnIlluminateRequest(): void
    {
        // Assert
        $this->expectException(InvalidRequestException::class);

        // Act
        (new UriPathStrategy(0))->resolve(
            new SymfonyRequest()
        );
    }
}
