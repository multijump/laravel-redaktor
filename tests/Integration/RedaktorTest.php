<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor;

use DSLabs\LaravelRedaktor\Version\HeaderResolver;
use DSLabs\LaravelRedaktor\Version\QueryStringResolver;
use DSLabs\Redaktor\ChiefEditor;
use DSLabs\Redaktor\ChiefEditorInterface;
use DSLabs\Redaktor\Registry\InMemoryRegistry;
use DSLabs\Redaktor\Registry\Registry;
use DSLabs\Redaktor\Version\VersionResolver;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase;

final class RedaktorTest extends TestCase
{
    public function testDefaultConfigurationFormat(): void
    {
        // Act
        $redaktorConfig = $this->app->get('config')->get('redaktor');

        // Assert
        self::assertIsArray($redaktorConfig);
        self::assertArrayHasKey('resolver', $redaktorConfig);
        self::assertArrayHasKey('id', $resolver = $redaktorConfig['resolver']);
        self::assertArrayHasKey('config', $resolver);
        self::assertArrayHasKey('name', $resolver['config']);

        self::assertArrayHasKey('revisions', $redaktorConfig);
    }

    public function testDefaultsToHeaderResolver(): void
    {
        // Act
        $resolverConfig = $this->app->get('config')->get('redaktor.resolver');

        // Assert
        self::assertSame(HeaderResolver::class, $resolverConfig['id']);
        self::assertSame(['name' => 'API-Version'], $resolverConfig['config']);
    }

    public function testDefaultsToAnEmptyRevisionsList(): void
    {
        // Act
        $revisions = $this->app->get('config')->get('redaktor.revisions');

        // Assert
        self::assertSame([], $revisions);
    }

    public function testVersionResolverIsNotInstantiable(): void
    {
        // Arrange
        $this->app->get('config')->set('redaktor.resolver.id', 'foo');

        // Assert
        $this->expectException(BindingResolutionException::class);

        // Act
        $this->app->make(VersionResolver::class);
    }

    public function testVersionResolverDoesNotImplementVersionResolverInterface(): void
    {
        // Arrange
        $this->app->get('config')->set('redaktor.resolver.id', get_class(new class {}));

        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        $this->app->make(VersionResolver::class);
    }

    public function testRetrievesRevisionNameUsingDefaultResolver(): void
    {
        // Arrange
        $request = new Request();
        $request->headers->set('API-Version', 'foo');

        // Act
        $revisionName = $this->app->get(VersionResolver::class)->resolve($request);

        // Assert
        self::assertSame('foo', $revisionName);
    }

    public function testRetrievesRevisionNameUsingConfiguredResolver(): void
    {
        // Arrange
        $config = $this->app->get('config');
        $config->set('redaktor.resolver', [
            'id' => QueryStringResolver::class,
            'config' => [
                'name' => 'foo'
            ]
        ]);

        $request = new Request([
            'foo' => 'bar',
        ]);

        // Act
        $revisionName = $this->app->get(VersionResolver::class)->resolve($request);

        // Assert
        self::assertSame('bar', $revisionName);
    }

    public function testPublishesConfig(): void
    {
        // Arrange
        $publishedConfigFilePath = $this->app->configPath('redaktor.php');
        @unlink($publishedConfigFilePath);

        // Act
        Artisan::call('vendor:publish', [
            '--provider' => RedaktorServiceProvider::class
        ]);

        // Assert
        self::assertFileEquals(__DIR__ . '/../../src/config/redaktor.php', $publishedConfigFilePath);
        @unlink($publishedConfigFilePath);
    }

    public function testBindsInMemoryRegistryToEmptyRevisionsRegistryByDefault(): void
    {
        // Act
        $registry = $this->app->make(InMemoryRegistry::class);

        // Assert
        self::assertEmpty($registry->retrieveAll());
    }

    public function testInMemoryRegistryWithNoRevisions(): void
    {
        // Act
        $registry = $this->app->make(InMemoryRegistry::class);

        // Assert
        self::assertEmpty($registry->retrieveAll());
    }

    public function testInMemoryRegistryIsConfigured(): void
    {
        // Arrange
        $this->app->get('config')->set(
            'redaktor.revisions',
            [
                'foo' => [
                    static function() { },
                    static function() { },
                ]
            ]
        );

        // Act
        $registry = $this->app->make(InMemoryRegistry::class);

        // Assert
        self::assertCount(2, $registry->retrieveAll());
    }

    public function testRegistryDefaultsToInMemoryRegistry(): void
    {
        // Act
        $registry = $this->app->make(Registry::class);

        // Assert
        self::assertInstanceOf(InMemoryRegistry::class, $registry);
    }

    public function testBindsChiefEditor(): void
    {
        // Act
        $chiefEditor = $this->app->make(ChiefEditor::class);

        // Assert
        self::assertInstanceOf(ChiefEditor::class, $chiefEditor);
        self::assertInstanceOf(ChiefEditorInterface::class, $chiefEditor);
    }

    public function testBindsChiefEditorInterface(): void
    {
        // Act
        $chiefEditor = $this->app->make(ChiefEditorInterface::class);

        // Assert
        self::assertInstanceOf(ChiefEditorInterface::class, $chiefEditor);
        self::assertInstanceOf(ChiefEditor::class, $chiefEditor);
    }

    protected function getPackageProviders($app): array
    {
        return [
            RedaktorServiceProvider::class,
        ];
    }
}