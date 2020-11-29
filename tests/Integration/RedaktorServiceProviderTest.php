<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Integration;

use DSLabs\LaravelRedaktor\RedaktorServiceProvider;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithApplication;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithConfiguration;
use DSLabs\LaravelRedaktor\Version\CustomHeaderResolver;
use DSLabs\LaravelRedaktor\Version\QueryStringResolver;
use DSLabs\LaravelRedaktor\Version\UriPathResolver;
use DSLabs\Redaktor\ChiefEditorInterface;
use DSLabs\Redaktor\Registry\InMemoryRegistry;
use DSLabs\Redaktor\Registry\Registry;
use DSLabs\Redaktor\Version\VersionResolver;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\TestCase;

/**
 * @see RedaktorServiceProvider
 */
final class RedaktorServiceProviderTest extends TestCase
{
    use InteractsWithApplication;
    use InteractsWithConfiguration;

    protected function getServiceProviders(Application $app): array
    {
        return [
            RedaktorServiceProvider::class,
        ];
    }

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

    public function testDefaultsToCustomHeaderResolver(): void
    {
        // Act
        $resolverConfig = $this->app->get('config')->get('redaktor.resolver');

        // Assert
        self::assertSame(CustomHeaderResolver::class, $resolverConfig['id']);
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
        $this->withConfig([
            'redaktor.resolver.id' => 'foo'
        ]);

        // Assert
        $this->expectException(BindingResolutionException::class);

        // Act
        $this->app->make(VersionResolver::class);
    }

    public function testVersionResolverDoesNotImplementVersionResolverInterface(): void
    {
        // Arrange
        $this->withConfig([
            'redaktor.resolver.id' => get_class(new class {}),
        ]);

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
        $versionResolver = $this->app->get(VersionResolver::class);
        $version = $versionResolver->resolve($request);

        // Assert
        self::assertInstanceOf(CustomHeaderResolver::class, $versionResolver);
        self::assertSame('foo', (string)$version);
    }

    public function testRetrievesRevisionNameUsingConfiguredCustomHeaderResolver(): void
    {
        // Arrange
        $this->withConfig([
            'redaktor.resolver' => [
                'id' => CustomHeaderResolver::class,
                'config' => [
                    'name' => 'version',
                ]
            ]
        ]);

        $request = new Request();
        $request->headers->set('version', 'foo');

        // Act
        $versionResolver = $this->app->get(VersionResolver::class);
        $version = $versionResolver->resolve($request);

        // Assert
        self::assertInstanceOf(CustomHeaderResolver::class, $versionResolver);
        self::assertSame('foo', (string)$version);
    }

    public function testRetrievesRevisionNameUsingConfiguredQueryStringResolver(): void
    {
        // Arrange
        $this->withConfig([
            'redaktor.resolver' => [
                'id' => QueryStringResolver::class,
                'config' => [
                    'name' => 'foo',
                ]
            ]
        ]);

        $request = new Request([
            'foo' => 'bar',
        ]);

        // Act
        $versionResolver = $this->app->get(VersionResolver::class);
        $version = $versionResolver->resolve($request);

        // Assert
        self::assertInstanceOf(QueryStringResolver::class, $versionResolver);
        self::assertSame('bar', (string)$version);
    }

    public function testRetrievesRevisionNameUsingConfiguredResolver(): void
    {
        // Arrange
        $this->withConfig([
            'redaktor.resolver' => [
                'id' => UriPathResolver::class,
                'config' => [
                    'index' => 0,
                ],
            ],
        ]);

        $request = Request::create('/foo/users');

        // Act
        $versionResolver = $this->app->get(VersionResolver::class);
        $version = $versionResolver->resolve($request);

        // Assert
        self::assertInstanceOf(UriPathResolver::class, $versionResolver);
        self::assertSame('foo', (string)$version);
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
        self::assertFileEquals(__DIR__ . '/../../config/redaktor.php', $publishedConfigFilePath);
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
        $this->withConfig([
            'redaktor.revisions' => [
                'foo' => [
                    static function () { },
                    static function () { },
                ]
            ]
        ]);

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

    public function testThereIsOnlyASingleChiefEditor()
    {
        // Act
        $instanceA = $this->app->make(ChiefEditorInterface::class);
        $instanceB = $this->app->make(ChiefEditorInterface::class);

        // Assert
        self::assertSame($instanceA, $instanceB);
    }
}