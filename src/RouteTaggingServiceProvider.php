<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor;

use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\RouteCollectionInterface;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class RouteTaggingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /**
         * Tag a route with one or more tags.
         */
        Route::macro(
            'tag',
            /**
             * @param $tag string|string[]
             */
            function ($tag): self {
                $wrappedTag = is_array($tag) ? $tag : [$tag];

                if ($wrappedTag !== array_filter($wrappedTag, 'is_string')) {
                    throw new \TypeError(
                        sprintf(
                            'Argument %s passed to %s::%s() must be of the type string|string[], %s given.',
                            1,
                            static::class,
                            __FUNCTION__,
                            is_object($tag)
                                ? get_class($tag)
                                : gettype($tag)
                        )
                    );
                }

                /** @var Route $this */
                if (!isset($this->tags)) {
                    $this->tags = [];
                }

                $this->tags = array_values(
                    array_unique(
                        array_merge($this->tags, $wrappedTag)
                    )
                );

                return $this;
            }
        );

        /**
         * Retrieve the list of tags.
         */
        Route::macro(
            'tags',
            function (): array {
                /** @var Route $this */
                if (!isset($this->tags)) {
                    $this->tags = [];
                }

                return $this->tags;
            }
        );

        /**
         * Check if the route has the given tag.
         */
        Route::macro(
            'hasTag',
            function (string $tag): bool {
                /** @var Route $this */
                if (!isset($this->tags)) {
                    $this->tags = [];
                }

                if (in_array($tag, $this->tags, true)) {
                    return true;
                }

                // Group tags.
                if (!$tags = $this->getAction('tags')) {
                    return false;
                }

                return in_array($tag, $tags, true);
            }
        );

        /**
         * Retrieves a RouteCollectionInterface containing all routes matching the given tag.
         */
        Router::macro('getByTag', function (string $tag): RouteCollection {

            /** @var Router $this */
            return array_reduce(
                $this->getRoutes()->getRoutes(),
                static function (RouteCollection $filteredRouteCollection, Route $route) use ($tag) {
                    if ($route->hasTag($tag)) {
                        $filteredRouteCollection->add($route);
                    }

                    return $filteredRouteCollection;
                },
                new RouteCollection()
            );
        });
    }
}
