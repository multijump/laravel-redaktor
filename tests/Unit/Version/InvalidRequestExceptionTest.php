<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit\Version;

use DSLabs\LaravelRedaktor\Version\InvalidRequestException;
use PHPUnit\Framework\TestCase;

/**
 * @see InvalidRequestException
 */
final class InvalidRequestExceptionTest extends TestCase
{
    public function testMakeFromAScalarArgument(): void
    {
        // Act
        $exception = InvalidRequestException::make(123);

        // Assert
        self::assertStringContainsString(
            'integer',
            $exception->getMessage()
        );
    }

    public function testMakeAnExceptionFromAnObject(): void
    {
        // Act
        $exception = InvalidRequestException::make(new \stdClass());

        // Assert
        self::assertStringContainsString(
            'stdClass',
            $exception->getMessage()
        );
    }
}
