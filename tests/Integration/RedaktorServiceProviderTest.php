<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Integration;

use DSLabs\LaravelRedaktor\RedaktorServiceProvider;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithApplication;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithConfiguration;
use DSLabs\LaravelRedaktor\Version\CustomHeaderStrategy;
use DSLabs\LaravelRedaktor\Version\InvalidStrategyIdException;
use DSLabs\LaravelRedaktor\Version\QueryStringStrategy;
use DSLabs\LaravelRedaktor\Version\UriPathStrategy;
use DSLabs\Redaktor\ChiefEditorInterface;
use DSLabs\Redaktor\Registry\InMemoryRegistry;
use DSLabs\Redaktor\Registry\Registry;
use DSLabs\Redaktor\Version\VersionResolver;
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

    public function testDefaultsToCustomHeaderResolver(): void
    {
        // Arrange
        $request = new Request();
        $request->headers->set('API-Version', 'foo');

        // Act
        $version = $this->app->make(VersionResolver::class)
            ->resolve($request);

        // Assert
        self::assertSame('foo', (string)$version);
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
            'redaktor.strategies' => [
                [
                    'id' => 'foo',
                    'config' => [],
                ],
            ],
        ]);

        // Assert
        $this->expectException(InvalidStrategyIdException::class);

        // Act
        $this->app->make(VersionResolver::class);
    }

    public function testVersionResolverDoesNotImplementVersionResolverInterface(): void
    {
        // Arrange
        $this->withConfig([
            'redaktor.strategies' => [
                [
                    'id' => get_class(new class() {
                    }),
                    'config' => [],
                ],
            ],
        ]);

        // Assert
        $this->expectException(InvalidStrategyIdException::class);

        // Act
        $this->app->make(VersionResolver::class);
    }

    public function testRetrievesRevisionNameUsingDefaultResolver(): void
    {
        // Arrange
        $request = new Request();
        $request->headers->set('API-Version', 'foo');

        // Act
        $version = $this->app->get(VersionResolver::class)
            ->resolve($request);

        // Assert
        self::assertSame('foo', (string)$version);
    }

    public function testRetrievesRevisionNameUsingConfiguredCustomHeaderResolver(): void
    {
        // Arrange
        $this->withConfig([
            'redaktor.strategies' => [
                [
                    'id' => CustomHeaderStrategy::class,
                    'config' => [
                        'name' => 'X-Version',
                    ],
                ],
            ],
        ]);

        $request = new Request();
        $request->headers->set('X-Version', 'foo');

        // Act
        $version = $this->app->get(VersionResolver::class)
            ->resolve($request);

        // Assert
        self::assertSame('foo', (string)$version);
    }

    public function testRetrievesRevisionNameUsingConfiguredQueryStringResolver(): void
    {
        // Arrange
        $this->withConfig([
            'redaktor.strategies' => [
                [
                    'id' => QueryStringStrategy::class,
                    'config' => [
                        'name' => 'foo',
                    ],
                ],
            ],
        ]);

        $request = new Request([
            'foo' => 'bar',
        ]);

        // Act
        $version = $this->app->get(VersionResolver::class)
            ->resolve($request);

        // Assert
        self::assertSame('bar', (string)$version);
    }

    public function testRetrievesRevisionNameUsingConfiguredResolver(): void
    {
        // Arrange
        $this->withConfig([
            'redaktor.strategies' => [
                [
                    'id' => UriPathStrategy::class,
                    'config' => [
                        'index' => 0,
                    ],
                ],
            ],
        ]);

        $request = Request::create('/foo/users');

        // Act
        $version = $this->app->get(VersionResolver::class)
            ->resolve($request);

        // Assert
        self::assertSame('foo', (string)$version);
    }

    public function testPublishesConfig(): void
    {
        // Arrange
        $publishedConfigFilePath = $this->app->configPath('redaktor.php');
        @unlink($publishedConfigFilePath);

        // Act
        Artisan::call('vendor:publish', [
            '--provider' => RedaktorServiceProvider::class,
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
                ],
            ],
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

    protected function getServiceProviders(Application $app): array
    {
        return [
            RedaktorServiceProvider::class,
        ];
    }
}
