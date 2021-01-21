<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor;

use DSLabs\LaravelRedaktor\Department\IlluminateMessageDepartment;
use DSLabs\LaravelRedaktor\Department\IlluminateRoutingDepartment;
use DSLabs\LaravelRedaktor\Middleware\MessageRedaktor;
use DSLabs\LaravelRedaktor\Middleware\RoutingRedaktor;
use DSLabs\Redaktor\ChiefEditor;
use DSLabs\Redaktor\ChiefEditorInterface;
use DSLabs\Redaktor\Department\GenericMessageDepartment;
use DSLabs\Redaktor\Department\GenericRoutingDepartment;
use DSLabs\Redaktor\Department\MessageDepartment;
use DSLabs\Redaktor\Department\RoutingDepartment;
use DSLabs\Redaktor\Registry\InMemoryRegistry;
use DSLabs\Redaktor\Registry\PSR11RevisionResolver;
use DSLabs\Redaktor\Registry\Registry;
use DSLabs\Redaktor\Registry\RevisionResolver;
use DSLabs\Redaktor\Version\Strategy;
use DSLabs\Redaktor\Version\VersionResolver;
use Illuminate\Container\Container;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

final class RedaktorServiceProvider extends ServiceProvider
{
    private const SOURCE_CONFIG_PATH = __DIR__ . '/../config/redaktor.php';

    public function register(): void
    {
        $this->setupVersionResolver();
        $this->setupRevisionsRegistry();
        $this->setupEditorProviders();
        $this->setupChiefEditor();
    }

    public function boot(): void
    {
        $this->setupConfiguration();
        $this->setupMiddlewares();
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
            static function (Container $container): VersionResolver {
                $strategiesConfig = $container->get('config')->get('redaktor.strategies');

                $strategies = array_map(
                    static function (array $strategyConfig) use ($container) {
                        $strategy = $container->make(
                            $strategyConfig['id'],
                            $strategyConfig['config']
                        );

                        if (!$strategy instanceof Strategy) {
                            // @todo: improve exception message.
                            throw new \InvalidArgumentException();
                        }

                        return $strategy;
                    },
                    $strategiesConfig
                );

                return new VersionResolver($strategies);
            }
        );
    }

    private function setupEditorProviders(): void
    {
        $this->app->bind(IlluminateMessageDepartment::class, static function () {
            return new IlluminateMessageDepartment(
                new GenericMessageDepartment()
            );
        });
        $this->app->bind(IlluminateRoutingDepartment::class, static function () {
            return new IlluminateRoutingDepartment(
                new GenericRoutingDepartment()
            );
        });

        $this->app->bind(MessageDepartment::class, IlluminateMessageDepartment::class);
        $this->app->bind(RoutingDepartment::class, IlluminateRoutingDepartment::class);
    }

    private function setupRevisionsRegistry(): void
    {
        $this->app->singleton(InMemoryRegistry::class, static function (Container $container) {
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

        $laravelVersion = $this->app->version();
        if (version_compare($laravelVersion, '6.9', '>=')) {
            $kernel->appendMiddlewareToGroup('api', MessageRedaktor::class);
        } else {
            self::appendMiddlewareToGroup($kernel, 'api', MessageRedaktor::class);
        }
    }

    private static function appendMiddlewareToGroup(Kernel $kernel, string $group, string $middleware): void
    {
        $appendMiddlewareToGroup = (function (string $group, string $middleware) {
            if (! isset($this->middlewareGroups[$group])) {
                throw new \InvalidArgumentException("The [{$group}] middleware group has not been defined.");
            }

            if (array_search($middleware, $this->middlewareGroups[$group], true) === false) {
                $this->middlewareGroups[$group][] = $middleware;
            }

            $this->router->middlewarePriority = $this->middlewarePriority;

            foreach ($this->middlewareGroups as $key => $middleware) {
                $this->router->middlewareGroup($key, $middleware);
            }

            foreach ($this->routeMiddleware as $key => $middleware) {
                $this->router->aliasMiddleware($key, $middleware);
            }

            return $this;
        })->bindTo($kernel, get_class($kernel));

        $appendMiddlewareToGroup($group, $middleware);
    }
}
