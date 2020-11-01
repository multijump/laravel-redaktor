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
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

final class IlluminateRoutingEditorTest extends TestCase
{
    public function testRetrieveTheBriefedVersion(): void
    {
        // Arrange
        /** @var RoutingEditorInterface|ObjectProphecy $juniorRoutingEditor */
        $juniorRoutingEditor = $this->prophesize(RoutingEditorInterface::class);
        $juniorRoutingEditor->briefedVersion()
            ->willReturn($expectedVersion = new Version('foo'));

        // Act
        $actualVersion = (new IlluminateRoutingEditor($juniorRoutingEditor->reveal()))->briefedVersion();

        // Assert
        self::assertSame($expectedVersion, $actualVersion);
    }

    public function testRetrieveTheBriefedRevisions(): void
    {
        // Arrange
        /** @var RoutingEditorInterface|ObjectProphecy $juniorRoutingEditor */
        $juniorRoutingEditor = $this->prophesize(RoutingEditorInterface::class);
        $juniorRoutingEditor->briefedRevisions()
            ->willReturn($expectedRevisions = [
                new class implements Revision {},
                new class implements Revision {},
            ]);

        $illuminateEditor = new IlluminateRoutingEditor($juniorRoutingEditor->reveal());

        // Act
        $actualRevisions = $illuminateEditor->briefedRevisions();

        // Assert
        self::assertSame($expectedRevisions, $actualRevisions);
    }

    public function testRefuseARouteCollectionInstanceOtherThanAnIlluminateOne(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(RoutingEditorInterface::class);
        $illuminateEditor = new IlluminateRoutingEditor($juniorEditor->reveal());

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $illuminateEditor->reviseRouting(new SymfonyRouteCollection());
    }

    public function testRefuseReturningARouteCollectionInstanceOtherThanAnIlluminateOne(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(RoutingEditorInterface::class);
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
        $juniorEditor = $this->prophesize(RoutingEditorInterface::class);
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
        $juniorEditor = $this->prophesize(RoutingEditorInterface::class);
        $juniorEditor->reviseRouting(Argument::any())
            ->willReturn($juniorRevisedRouteCollection = new RouteCollection());

        $illuminateEditor = new IlluminateRoutingEditor($juniorEditor->reveal());

        // Act
        $revisedRouteCollection = $illuminateEditor->reviseRouting(new RouteCollection());

        // Assert
        self::assertSame($juniorRevisedRouteCollection, $revisedRouteCollection);
    }

//    public function testReviseIlluminateRoutes(): void
//    {
//        // Arrange
//        /** @var RoutingEditorInterface|ObjectProphecy $juniorRoutingEditor */
//        $juniorRoutingEditor = $this->prophesize(RoutingEditorInterface::class);
//        $juniorRoutingEditor->reviseRouting(Argument::type(IlluminateRouteCollection::class))
//            ->willReturn($expectedRoutes = new IlluminateRouteCollection());
//
//        // Act
//        $actualRoutes = (new IlluminateRoutingEditor($juniorRoutingEditor->reveal()))
//            ->reviseRouting(new IlluminateRouteCollection());
//
//        // Assert
//        self::assertSame($expectedRoutes, $actualRoutes);
//    }
//
//    public function testRejectsRevisingNonIlluminateRoutes(): void
//    {
//        // Arrange
//        /** @var RoutingEditorInterface|ObjectProphecy $juniorRoutingEditor */
//        $juniorRoutingEditor = $this->prophesize(RoutingEditorInterface::class);
//
//        // Assert
//        $this->expectException(\InvalidArgumentException::class);
//
//        // Act
//        (new IlluminateRoutingEditor($juniorRoutingEditor->reveal()))
//            ->reviseRouting(new SymfonyRouteCollection());
//    }
//
//    public function testRefuseARevisedNonIlluminateRouteCollection(): void
//    {
//        // Arrange
//        /** @var RoutingEditorInterface|ObjectProphecy $juniorRoutingEditor */
//        $juniorRoutingEditor = $this->prophesize(RoutingEditorInterface::class);
//        $juniorRoutingEditor->reviseRouting(Argument::any())
//            ->willReturn(new SymfonyRouteCollection());
//
//        // Assert
//        $this->expectException(\InvalidArgumentException::class);
//
//        // Act
//        (new IlluminateRoutingEditor($juniorRoutingEditor->reveal()))
//            ->reviseRouting(new IlluminateRouteCollection());
//    }
}