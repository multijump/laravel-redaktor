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
}
