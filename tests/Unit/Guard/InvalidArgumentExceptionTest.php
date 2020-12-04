<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit\Guard;

use DSLabs\LaravelRedaktor\Guard\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @see InvalidArgumentException
 */
final class InvalidArgumentExceptionTest extends TestCase
{
    public function testProvideAnEmptyListOfAllowedTypes(): void
    {
        // Assert
        $this->expectException(\UnexpectedValueException::class);

        // Act
        new InvalidArgumentException([], 'bar');
    }

    public function testProvideListOfAllowedTypesWithASingleType(): void
    {
        // Act
        $exception = new InvalidArgumentException(['foo'], 'bar');

        // Assert
        self::assertSame(
            'Instance of foo expected. Got string.',
            $exception->getMessage()
        );
    }

    public function testProvideListOfAllowedTypesWithAMultipleTypes(): void
    {
        // Act
        $exception = new InvalidArgumentException(['foo', 'bar'], 'baz');

        // Assert
        self::assertSame(
            'Instance of foo or bar expected. Got string.',
            $exception->getMessage()
        );
    }
}
