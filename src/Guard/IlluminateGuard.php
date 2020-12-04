<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Guard;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\RouteCollection;

/**
 * Guards the system against non-Illuminate instances.
 */
final class IlluminateGuard
{
    /**
     * Prevent instantiation.
     */
    private function __construct()
    {
    }

    /**
     * Ensures the parameter is an instance of \Illuminate\Routing\RouteCollection.
     *
     * @param $routes mixed
     * @throws InvalidArgumentException
     */
    public static function assertRouteCollection($routes): void
    {
        if (!$routes instanceof RouteCollection) {
            throw new InvalidArgumentException([RouteCollection::class], $routes);
        }
    }

    /**
     * Ensures the parameter is an instance of \Illuminate\Http\Request.
     *
     * @param $request mixed
     * @throws InvalidArgumentException
     */
    public static function assertRequest($request): void
    {
        if (!$request instanceof Request) {
            throw new InvalidArgumentException([Request::class], $request);
        }
    }

    /**
     * Ensures the parameter is an instance of a supported Response.
     *
     * @param $response mixed
     * @throws InvalidArgumentException
     */
    public static function assertResponse($response): void
    {
        if (!$response instanceof Response &&
            !$response instanceof JsonResponse
        ) {
            throw new InvalidArgumentException(
                [Response::class, JsonResponse::class],
                $response
            );
        }
    }
}
