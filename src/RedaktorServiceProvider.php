<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor;

use DSLabs\LaravelRedaktor\Middleware\MessageRedaktor;
use DSLabs\LaravelRedaktor\Middleware\RoutingRedaktor;
use DSLabs\Redaktor\ChiefEditor;
use DSLabs\Redaktor\ChiefEditorInterface;
use DSLabs\Redaktor\Department\MessageDepartment;
use DSLabs\Redaktor\Department\RoutingDepartment;
use DSLabs\Redaktor\Registry\InMemoryRegistry;
use DSLabs\Redaktor\Registry\PSR11RevisionResolver;
use DSLabs\Redaktor\Registry\Registry;
use DSLabs\Redaktor\Registry\RevisionResolver;
use DSLabs\Redaktor\Version\VersionResolver;
use Illuminate\Container\Container;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\RouteCollectionInterface;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

final class RedaktorServiceProvider extends ServiceProvider
{
    private const SOURCE_CONFIG_PATH = __DIR__ . '/../config/redaktor.php';

    public function register(): void
    {
        $this->setupConfiguration();
        $this->setupVersionResolver();
        $this->setupRevisionsRegistry();
        $this->setupEditorProviders();
        $this->setupChiefEditor();
    }

    public function boot(): void
    {
        $this->setupMiddlewares();
        self::setupRouteMacros();
    }

    private function setupConfiguration(): void
    {
        $this->mergeConfigFrom(self::SOURCE_CONFIG_PATH, 'redaktor');

        $this->publishes([
            self::SOURCE_CONFIG_PATH => $this->app->configPath('redaktor.php'),
        ]);
    }

    private function setupVersionResolver(): void
    {
        $this->app->singleton(
            VersionResolver::class,
            static function(Container $container): VersionResolver {
                $resolverConfig = $container->get('config')->get('redaktor.resolver');

                $versionResolver = $container->make(
                    $resolverConfig['id'],
                    $resolverConfig['config']
                );

                if (!$versionResolver instanceof VersionResolver) {
                    // @todo: improve exception message.
                    throw new \InvalidArgumentException();
                }

                return $versionResolver;
            }
        );
    }

    private function setupEditorProviders(): void
    {
        $this->app->singleton(MessageDepartment::class);
        $this->app->singleton(RoutingDepartment::class);
    }

    private function setupRevisionsRegistry(): void
    {
        $this->app->singleton(InMemoryRegistry::class, static function(Container $container) {
            $revisions = $container->get('config')->get('redaktor.revisions');

            return new InMemoryRegistry($revisions);
        });

        $this->app->alias(InMemoryRegistry::class, Registry::class);

        $this->app->singleton(RevisionResolver::class, PSR11RevisionResolver::class);
    }

    private function setupChiefEditor(): void
    {
        $this->app->singleton(ChiefEditor::class);
        $this->app->alias(ChiefEditor::class, ChiefEditorInterface::class);
    }

    private function setupMiddlewares(): void
    {
        /** @var \App\Http\Kernel $kernel */
        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(RoutingRedaktor::class);
        $kernel->appendMiddlewareToGroup('api', MessageRedaktor::class);
    }

    private static function setupRouteMacros(): void
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

                if($wrappedTag !== array_filter($wrappedTag, 'is_string')) {
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

                return in_array($tag,  $tags, true);
            }
        );

        /**
         * Retrieves a RouteCollectionInterface containing all routes matching the given tag.
         */
        Router::macro('getByTag', function (string $tag): RouteCollectionInterface {

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