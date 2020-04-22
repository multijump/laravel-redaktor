<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor;

use DSLabs\Redaktor\ChiefEditor;
use DSLabs\Redaktor\ChiefEditorInterface;
use DSLabs\Redaktor\Registry\InMemoryRegistry;
use DSLabs\Redaktor\Registry\Registry;
use DSLabs\Redaktor\Version\VersionResolver;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class RedaktorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->setupConfiguration();
        self::setupVersionResolver($this->app);
        self::configureRevisionsRegistry($this->app);
        self::setupChiefEditor($this->app);
    }

    private function setupConfiguration(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/redaktor.php', 'redaktor');

        $this->publishes([
            __DIR__ . '/config/redaktor.php' => $this->app->configPath('redaktor.php'),
        ]);
    }

    private static function setupVersionResolver(Application $app): void
    {
        $app->singleton(
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

    private static function setupChiefEditor(Application $app): void
    {
        $app->singleton(ChiefEditor::class, ChiefEditor::class);

        $app->singleton(IlluminateChiefEditor::class, static function (Application $app) {
            return new IlluminateChiefEditor(
                $app->make(ChiefEditor::class)
            );
        });
        $app->alias(IlluminateChiefEditor::class, ChiefEditorInterface::class);
    }

    private static function configureRevisionsRegistry(Application $app): void
    {
        $app->singleton(InMemoryRegistry::class, static function(Container $container) {
            $revisions = $container->get('config')->get('redaktor.revisions');

            return new InMemoryRegistry($revisions);
        });

        $app->alias(InMemoryRegistry::class, Registry::class);
    }
}