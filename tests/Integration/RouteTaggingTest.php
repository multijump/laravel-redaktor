<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Integration;

use DSLabs\LaravelRedaktor\RouteTaggingServiceProvider;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithApplication;
use DSLabs\LaravelRedaktor\Tests\Concerns\InteractsWithRouting;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
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
        $router->get('/foo')->tag('bar');
        $router->post('/baz')->tag('quz');

        // Act
        $taggedRoutes = $router->getByTag('foobar');

        // Assert
        self::assertInstanceOf(RouteCollection::class, $taggedRoutes);
        self::assertCount(0, $taggedRoutes);
    }

    public function testRetrieveRoutesForTag(): void
    {
        // Arrange
        $router = $this->getRouter();
        $fooRoute = $router->get('/foo')->tag('foobar');
        $barRoute = $router->post('/bar')->tag('baz');
        $bazRoute = $router->put('/baz')->tag('foobar');

        // Act
        $taggedRoutes = $router->getByTag('foobar');

        // Assert
        self::assertInstanceOf(RouteCollection::class, $taggedRoutes);
        self::assertCount(2, $taggedRoutes);

        self::assertContains($fooRoute, $taggedRoutes->getRoutes());
        self::assertNotContains($barRoute, $taggedRoutes->getRoutes());
        self::assertContains($bazRoute, $taggedRoutes->getRoutes());
    }

    public function testTagRoutesGroup(): void
    {
        // Arrange
        $router = $this->getRouter();

        // Act
        $barRoute = $bazRoute = null;
        $fooRoute = $router->get('/foo');
        $router->group(
            [
                'tags' => ['barbaz'],
            ],
            static function (Router $router) use (&$barRoute, &$bazRoute): void {
                $barRoute = $router->post('/bar');
                $bazRoute = $router->put('/baz');
            }
        );

        // Assert
        self::assertCount(2, $barbazRoutes = $router->getByTag('barbaz'));
        self::assertNotContains($fooRoute, $barbazRoutes);
        self::assertContains($barRoute, $barbazRoutes);
        self::assertContains($bazRoute, $barbazRoutes);
    }

    public function testTagSubGroups(): void
    {
        // Arrange
        $router = $this->getRouter();

        // Act
        $fooRoute = $barRoute = null;
        $router->group(
            [
                'tags' => ['foobar'],
            ],
            static function (Router $router) use (&$fooRoute, &$barRoute): void {
                $fooRoute = $router->get('/foo');

                $router->group(
                    [
                        'tags' => ['bar'],
                    ],
                    static function (Router $router) use (&$barRoute): void {
                        $barRoute = $router->post('/bar');
                    }
                );
            }
        );

        // Assert
        self::assertCount(2, $fooBarRoutes = $router->getByTag('foobar'));
        self::assertContains($fooRoute, $fooBarRoutes);
        self::assertContains($barRoute, $fooBarRoutes);
        self::assertCount(1, $barRoutes = $router->getByTag('bar'));
        self::assertNotContains($fooRoute, $barRoutes);
        self::assertContains($barRoute, $barRoutes);
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
            RouteTaggingServiceProvider::class,
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
