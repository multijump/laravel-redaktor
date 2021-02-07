<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Integration\Version;

use DSLabs\LaravelRedaktor\Guard\InvalidArgumentException;
use DSLabs\LaravelRedaktor\RedaktorServiceProvider;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithApplication;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithDatabase;
use DSLabs\LaravelRedaktor\Version\DatabaseStrategy;
use DSLabs\Redaktor\Version\UnresolvedVersionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\TestCase;

/**
 * @see DatabaseStrategy
 */
final class DatabaseStrategyTest extends TestCase
{
    use InteractsWithApplication;
    use InteractsWithDatabase;

    public function testAnExceptionIsThrownIfNoQueryBuilderIsSetBeforeResolvingTheVersion(): void
    {
        // Arrange
        $strategy = new DatabaseStrategy();

        // Assert
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('/setQueryBuilder/');

        // Act
        $strategy->resolve(new Request());
    }

    public function testThrowsAnExceptionIfTryingToResolveFromANonIlluminateRequest(): void
    {
        // Arrange
        $strategy = new DatabaseStrategy();
        $strategy->withQueryBuilder(
            $this->getQueryBuilder()
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $strategy->resolve(new \stdClass());
    }

    public function testThrowAnUnresolvedVersionExceptionIfNoIdentifierSent(): void
    {
        // Arrange
        Schema::create('redaktor', static function (Blueprint $table): void {
            $table->string('app_id')->nullable(false)->unique();
            $table->string('version')->nullable(false);
        });
        $strategy = new DatabaseStrategy();
        $strategy->withQueryBuilder(
            $this->getQueryBuilder()
        );

        // Assert
        $this->expectException(UnresolvedVersionException::class);

        // Act
        $strategy->resolve(new Request());
    }

    public function testThrowAnUnresolvedVersionExceptionIfNoIdentifierMatched(): void
    {
        // Arrange
        Schema::create('redaktor', static function (Blueprint $table): void {
            $table->string('app_id')->nullable(false)->unique();
            $table->string('version')->nullable(false);
        });
        $strategy = new DatabaseStrategy();
        $strategy->withQueryBuilder(
            $this->getQueryBuilder()
        );

        // Assert
        $this->expectException(UnresolvedVersionException::class);

        // Act
        $request = new Request();
        $request->headers->set('Application-Id', 'foo');
        $strategy->resolve($request);
    }

    public function testFetchVersionUsingDefaultValues(): void
    {
        // Arrange
        Schema::create('redaktor', static function (Blueprint $table): void {
            $table->string('app_id')->nullable(false)->unique();
            $table->string('version')->nullable(false);
        });
        $this->insertInto(
            'redaktor',
            [
                'version' => $expectedVersion = 'foo',
                'app_id' => $appId = 'bar',
            ]
        );

        $strategy = new DatabaseStrategy();
        $strategy->withQueryBuilder(
            $this->getQueryBuilder()
        );

        // Act
        $request = Request::create('/');
        $request->headers->set('Application-Id', $appId);
        $version = $strategy->resolve($request);

        // Assert
        self::assertSame($expectedVersion, (string)$version);
    }

    public function testFetchVersionFromCustomColumnAndTable(): void
    {
        // Arrange
        $column = 'baz';
        Schema::create($table = 'quz', static function (Blueprint $table) use ($column): void {
            $table->string('app_id')->nullable(false)->unique();
            $table->string($column)->nullable(false);
        });
        $this->insertInto(
            $table,
            [
                $column => $expectedVersion = 'foo',
                'app_id' => $appId = 'bar',
            ]
        );

        $strategy = new DatabaseStrategy($table, $column);
        $strategy->withQueryBuilder(
            $this->getQueryBuilder()
        );

        // Act
        $request = Request::create('/');
        $request->headers->set('Application-Id', $appId);
        $version = $strategy->resolve($request);

        // Assert
        self::assertSame($expectedVersion, (string)$version);
    }

    public function testFetchVersionUsingCustomFilterDefinedAtInstantiation(): void
    {
        // Arrange
        $appIdColumn = 'foo';
        Schema::create('redaktor', static function (Blueprint $table) use ($appIdColumn): void {
            $table->string($appIdColumn)->nullable(false)->unique();
            $table->string('version')->nullable(false);
        });
        $this->insertInto(
            'redaktor',
            [
                'version' => $expectedVersion = 'bar',
                $appIdColumn => $appId = 'baz',
            ]
        );

        $strategy = new DatabaseStrategy(
            'redaktor',
            'version',
            static function () use ($appIdColumn, $appId): array {
                return [
                    $appIdColumn => $appId,
                ];
            }
        );
        $strategy->withQueryBuilder(
            $this->getQueryBuilder()
        );

        // Act
        $version = $strategy->resolve(Request::create('/'));

        // Assert
        self::assertSame($expectedVersion, (string)$version);
    }

    protected function getServiceProviders(Application $app): array
    {
        return [
            RedaktorServiceProvider::class,
        ];
    }
}
