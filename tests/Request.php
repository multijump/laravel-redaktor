<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests;

use Illuminate\Http\Request as IlluminateRequest;

/**
 * Illuminate Request drop-in replacement that provides some additional helper
 * methods to make tests more readable.
 */
final class Request extends IlluminateRequest
{
    /**
     * Creates a request with the API-Version header set to the version
     * specified in $version.
     */
    public static function forVersion(string $version): self
    {
        return new self(
            [],
            [],
            [],
            [],
            [],
            ['HTTP_API-Version' => $version ]
        );
    }

    public static function createForVersion(string $version, string $uri, string $method = 'GET'): self
    {
        return self::create(
            $uri,
            $method,
            [],
            [],
            [],
            ['HTTP_API-Version' => $version ]
        );
    }
}