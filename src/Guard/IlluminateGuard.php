<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Guard;

use Illuminate\Http\JsonResponse;
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
            self::throwException([RouteCollection::class], $routes);
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
            self::throwException([Request::class], $request);
        }
    }

    /**
     * Ensures the parameter is an instance of \Illuminate\Http\Response.
     *
     * @throws InvalidArgumentException
     */
    public static function assertResponse($response): void
    {
        if (!$response instanceof Response &&
            !$response instanceof JsonResponse
        ) {
            self::throwException([Response::class, JsonResponse::class], $response);
        }
    }

    /**
     * @param array $expectedInstances
     * @param $value
     *
     */
    private static function throwException(array $expectedInstances, $value): void
    {
        $last = array_pop($expectedInstances);
        $instancesString = implode(', ', $expectedInstances) . " or $last";

        throw new InvalidArgumentException(
            sprintf(
                'Instance of %s expected. Got %s.',
                $instancesString,
                is_object($value)
                    ? get_class($value)
                    : gettype($value)
            )
        );
    }
}