<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit\Editor;

use DSLabs\LaravelRedaktor\Editor\IlluminateRoutingEditor;
use DSLabs\Redaktor\Editor\RoutingEditorInterface;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Version\Version;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\RouteCollection as IlluminateRouteCollection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

/**
 * @see IlluminateRoutingEditor
 */
final class IlluminateRoutingEditorTest extends TestCase
{
    use ProphecyTrait;

    public function testRetrieveTheBriefedVersion(): void
    {
        // Arrange
        $juniorEditor = $this->createRoutingEditorProphecy();
        $juniorEditor->briefedVersion()
            ->willReturn($expectedVersion = new Version('foo'));
        $illuminateEditor = new IlluminateRoutingEditor($juniorEditor->reveal());

        // Act
        $briefedVersion = $illuminateEditor->briefedVersion();

        // Assert
        self::assertSame($expectedVersion, $briefedVersion);
    }

    public function testRetrieveTheBriefedRevisions(): void
    {
        // Arrange
        $juniorEditor = $this->createRoutingEditorProphecy();
        $juniorEditor->briefedRevisions()
            ->willReturn($expectedRevisions = [
                new class() implements Revision {
                },
                new class() implements Revision {
                },
            ]);
        $illuminateEditor = new IlluminateRoutingEditor($juniorEditor->reveal());

        // Act
        $briefedRevisions = $illuminateEditor->briefedRevisions();

        // Assert
        self::assertSame($expectedRevisions, $briefedRevisions);
    }

    public function testRefuseARouteCollectionInstanceOtherThanAnIlluminateOne(): void
    {
        // Arrange
        $juniorEditor = $this->createRoutingEditorProphecy();
        $illuminateEditor = new IlluminateRoutingEditor($juniorEditor->reveal());

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $illuminateEditor->reviseRouting(new SymfonyRouteCollection());
    }

    public function testRefuseReturningARouteCollectionInstanceOtherThanAnIlluminateOne(): void
    {
        // Arrange
        $juniorEditor = $this->createRoutingEditorProphecy();
        $juniorEditor->reviseRouting(Argument::any())
            ->willReturn(new SymfonyRouteCollection());
        $illuminateEditor = new IlluminateRoutingEditor($juniorEditor->reveal());

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $illuminateEditor->reviseRouting(new RouteCollection());
    }

    public function testDelegateRevisingTheRoutesToTheJuniorEditor(): void
    {
        // Arrange
        $juniorEditor = $this->createRoutingEditorProphecy();
        $juniorEditor->reviseRouting(Argument::any())
            ->willReturn(new RouteCollection());
        $illuminateEditor = new IlluminateRoutingEditor($juniorEditor->reveal());

        // Act
        $illuminateEditor->reviseRouting($originalRouteCollection = new RouteCollection());

        // Assert
        $juniorEditor->reviseRouting($originalRouteCollection)
            ->shouldHaveBeenCalled();
    }

    public function testJuniorEditorReportsBackTheRevisedRoutes(): void
    {
        // Arrange
        $juniorEditor = $this->createRoutingEditorProphecy();
        $juniorEditor->reviseRouting(Argument::any())
            ->willReturn($juniorRevisedRoutes = new RouteCollection());
        $illuminateEditor = new IlluminateRoutingEditor($juniorEditor->reveal());

        // Act
        $revisedRoutes = $illuminateEditor->reviseRouting(new RouteCollection());

        // Assert
        self::assertSame($juniorRevisedRoutes, $revisedRoutes);
    }

    public function testReviseIlluminateRoutes(): void
    {
        // Arrange
        $juniorEditor = $this->createRoutingEditorProphecy();
        $juniorEditor->reviseRouting(Argument::type(IlluminateRouteCollection::class))
            ->willReturn($juniorRevisedRoutes = new IlluminateRouteCollection());
        $illuminateEditor = new IlluminateRoutingEditor($juniorEditor->reveal());

        // Act
        $revisedRoutes = $illuminateEditor->reviseRouting(new IlluminateRouteCollection());

        // Assert
        self::assertSame($juniorRevisedRoutes, $revisedRoutes);
    }

    public function testRejectsRevisingNonIlluminateRoutes(): void
    {
        // Arrange
        $juniorEditor = $this->createRoutingEditorProphecy();
        $illuminateEditor = new IlluminateRoutingEditor($juniorEditor->reveal());

        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        $illuminateEditor->reviseRouting(new SymfonyRouteCollection());
    }

    public function testRefuseARevisedNonIlluminateRouteCollection(): void
    {
        // Arrange
        $juniorEditor = $this->createRoutingEditorProphecy();
        $juniorEditor->reviseRouting(Argument::any())
            ->willReturn(new SymfonyRouteCollection());
        $illuminateEditor = new IlluminateRoutingEditor($juniorEditor->reveal());

        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        $illuminateEditor->reviseRouting(new IlluminateRouteCollection());
    }

    /**
     * @return RoutingEditorInterface|ObjectProphecy
     */
    private function createRoutingEditorProphecy(): ObjectProphecy
    {
        return $this->prophesize(RoutingEditorInterface::class);
    }
}
