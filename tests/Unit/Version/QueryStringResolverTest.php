<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit\Version;

use DSLabs\LaravelRedaktor\Version\InvalidRequestException;
use DSLabs\LaravelRedaktor\Version\QueryStringResolver;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * @see QueryStringResolver
 */
final class QueryStringResolverTest extends TestCase
{
    public function testRetrievesEmptyVersionIfParameterIsNotDefined(): void
    {
        // Act
        $version = (new QueryStringResolver('foo'))->resolve(new Request());

        //Assert
        self::assertSame('', (string)$version);
    }

    public function testRetrievesRevisionName(): void
    {
        // Arrange
        $request = new Request(['foo' => 'bar']);

        // Act
        $version = (new QueryStringResolver('foo'))->resolve($request);

        // Assert
        self::assertSame('bar', (string)$version);
    }

    public function testThrowsAnExceptionIfTheArgumentIsNotAnIlluminateRequest(): void
    {
        // Assert
        $this->expectException(InvalidRequestException::class);

        // Act
        (new QueryStringResolver('Foo'))->resolve(
            new SymfonyRequest()
        );
    }
}
