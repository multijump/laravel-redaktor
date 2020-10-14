<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Integration;

use DSLabs\LaravelRedaktor\RedaktorServiceProvider;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithApplication;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithRouting;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollectionInterface;
use Illuminate\Routing\Router;
use PHPUnit\Framework\TestCase;

final class RouteTaggingTest extends TestCase
{
    use InteractsWithApplication;
    use InteractsWithRouting;

    public function testTagWithASingleTag(): void
    {
        // Arrange
        $route = new DummyRoute();

        // Act
        $taggedRoute = $route->tag('bar');

        // Assert
        self::assertSame($route, $taggedRoute);
        self::assertSame(['bar'], $route->tags());
    }

    /**
     * @dataProvider provideInvalidTag
     */
    public function testTagWithAnInvalidTag($tag): void
    {
        // Assert
        $this->expectException(\TypeError::class);

        // Act
        (new DummyRoute())->tag($tag);
    }

    public function testRetrieveTagsOnRouteWithNoTags(): void
    {
        // Act / Assert
        self::assertSame([], (new DummyRoute())->tags());
    }

    public function testTagWithAListOfTags(): void
    {
        // Act
        $route = (new DummyRoute())->tag(['bar', 'baz']);

        // Assert
        self::assertSame(['bar', 'baz'], $route->tags());
    }

    public function testAddASingleTagToTheList(): void
    {
        // Arrange
        $route = (new DummyRoute())
            ->tag(['bar', 'baz']);

        // Act
        $route->tag('foobar');

        // Assert
        self::assertSame(['bar', 'baz', 'foobar'], $route->tags());
    }

    public function testAddAListOfTagsToTheList(): void
    {
        // Arrange
        $route = (new DummyRoute())
            ->tag(['bar', 'baz']);

        // Act
        $route->tag(['foobar', 'foobaz']);

        // Assert
        self::assertSame(['bar', 'baz', 'foobar', 'foobaz'], $route->tags());
    }

    public function testDuplicatedTagsAreEliminatedWhenTagging(): void
    {
        // Act
        $route = (new DummyRoute())->tag(['bar', 'baz', 'bar']);

        // Assert
        self::assertSame(['bar', 'baz'], $route->tags());
    }

    public function testHasTag(): void
    {
        // Arrange
        $route = (new DummyRoute())
            ->tag(['bar', 'baz']);

        // Act / Assert
        self::assertTrue($route->hasTag('bar'));
        self::assertFalse($route->hasTag('foobar'));
    }

    public function testHasTagOnRouteWithNoTags(): void
    {
        // Arrange
        $route = new DummyRoute();

        // Act / Assert
        self::assertFalse($route->hasTag('foo'));
    }

    public function testRetrieveRoutesForNonExistingTag(): void
    {
        // Arrange
        $router = $this->getRouter();
        $router->addRoute('GET', '/foo', static function () { })->tag('bar');
        $router->addRoute('POST', '/bar', static function () { })->tag('baz');

        // Act
        $taggedRoutes = $router->getByTag('foobar');

        // Assert
        self::assertInstanceOf(RouteCollectionInterface::class, $taggedRoutes);
        self::assertCount(0, $taggedRoutes);
    }

    public function testRetrieveRoutesForTag(): void
    {
        // Arrange
        $router = $this->getRouter();
        $fooRoute = $router->addRoute('GET', '/foo', static function () { })->tag('foobar');
        $barRoute = $router->addRoute('POST', '/bar', static function () { })->tag('baz');
        $bazRoute = $router->addRoute('PUT', '/baz', static function () { })->tag('foobar');

        // Act
        $taggedRoutes = $router->getByTag('foobar');

        // Assert
        self::assertInstanceOf(RouteCollectionInterface::class, $taggedRoutes);
        self::assertCount(2, $taggedRoutes);

        self::assertContains($fooRoute, $taggedRoutes->getRoutes());
        self::assertNotContains($barRoute, $taggedRoutes->getRoutes());
        self::assertContains($bazRoute, $taggedRoutes->getRoutes());
    }

    public function provideInvalidTag(): array
    {
        return [
            'Null' => [null],
            'Integer' => [1],
            'Float' => [2.34],
            'Boolean' => [true],
            'Object' => [new \stdClass()],
            'Closure' => [static function () { }],
            'Null array' => [[null]],
            'Integer array' => [[1]],
            'Float array' => [[2.34]],
            'Boolean array' => [[true]],
            'Object array' => [[new \stdClass()]],
            'Closure array' => [[static function () { }]],
        ];
    }

    protected function getServiceProviders(Application $app): array
    {
        return [
            RedaktorServiceProvider::class,
        ];
    }
}

final class DummyRoute extends Route
{
    public function __construct()
    {
        parent::__construct(
            'GET',
            '/foo',
            static function () { }
        );
    }
}