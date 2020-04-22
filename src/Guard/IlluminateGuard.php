<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Guard;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\RouteCollection;
use InvalidArgumentException;

/**
 * Guards the system against non-Illuminate instances.
 */
final class IlluminateGuard
{
    /**
     * Prevent instantiation.
     */
    private function __construct() { }

    /**
     * Ensures the parameter is an instance of \Illuminate\Routing\RouteCollection.
     *
     * @throws InvalidArgumentException
     */
    public static function assertRouteCollection($routes): void
    {
        if (!$routes instanceof RouteCollection) {
            self::throwException(RouteCollection::class, $routes);
        }
    }

    /**
     * Ensures the parameter is an instance of \Illuminate\Http\Request.
     *
     * @throws InvalidArgumentException
     */
    public static function assertRequest($request): void
    {
        if (!$request instanceof Request) {
            self::throwException(Request::class, $request);
        }
    }

    /**
     * Ensures the parameter is an instance of \Illuminate\Http\Response.
     *
     * @throws InvalidArgumentException
     */
    public static function assertResponse($response): void
    {
        if (!$response instanceof Response) {
            self::throwException(Response::class, $response);
        }
    }

    /**
     * @param string $expectedInstance
     * @param $value
     *
     * @throws InvalidArgumentException
     */
    private static function throwException(string $expectedInstance, $value): void
    {
        throw new InvalidArgumentException(
            sprintf(
                'An instance of %s expected. Got %s.',
                $expectedInstance,
                is_object($value)
                    ? get_class($value)
                    : gettype($value)
            )
        );
    }
}