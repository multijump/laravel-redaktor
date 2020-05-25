<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor;

use DSLabs\Redaktor\ChiefEditor;
use DSLabs\Redaktor\ChiefEditorInterface;
use DSLabs\Redaktor\Department\EditorDepartment;
use DSLabs\Redaktor\Department\EditorProvider;
use DSLabs\Redaktor\Registry\InMemoryRegistry;
use DSLabs\Redaktor\Registry\Registry;
use DSLabs\Redaktor\Version\VersionResolver;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

final class RedaktorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->setupConfiguration();
        $this->setupVersionResolver();
        $this->setupRevisionsRegistry();
        $this->setupEditorProvider();
        $this->setupChiefEditor();
    }

    private function setupConfiguration(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/redaktor.php', 'redaktor');

        $this->publishes([
            __DIR__ . '/config/redaktor.php' => $this->app->configPath('redaktor.php'),
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

    private function setupEditorProvider(): void
    {
        $this->app->singleton(EditorProvider::class, EditorDepartment::class);
    }

    private function setupRevisionsRegistry(): void
    {
        $this->app->singleton(InMemoryRegistry::class, static function(Container $container) {
            $revisions = $container->get('config')->get('redaktor.revisions');

            return new InMemoryRegistry($revisions);
        });

        $this->app->alias(InMemoryRegistry::class, Registry::class);
    }

    private function setupChiefEditor(): void
    {
        $this->app->singleton(ChiefEditor::class);
        $this->app->alias(ChiefEditor::class, ChiefEditorInterface::class);
    }
}