<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit;

use DSLabs\LaravelRedaktor\IlluminateEditor;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Version\Version;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\RouteCollection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

/**
 * @see IlluminateEditor
 */
final class IlluminateEditorTest extends TestCase
{
    public function testRetrievesTheBriefedVersion(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(EditorInterface::class);
        $juniorEditor->briefedVersion()
            ->willReturn($expectedBriefedVersion = new Version('foo'));

        $illuminateEditor = new IlluminateEditor($juniorEditor->reveal());

        // Act
        $actualBriefedVersion = $illuminateEditor->briefedVersion();

        // Assert
        self::assertSame($expectedBriefedVersion, $actualBriefedVersion);
    }

    public function testRetrievesTheBriefedRevisions(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(EditorInterface::class);
        $juniorEditor->briefedRevisions()
            ->willReturn($revisions = [
                $this->prophesize(Revision::class)->reveal(),
            ]);

        $illuminateEditor = new IlluminateEditor($juniorEditor->reveal());

        // Act
        $briefedRevisions = $illuminateEditor->briefedRevisions();

        // Assert
        self::assertSame($revisions, $briefedRevisions);
    }

    public function testRefuseARouteCollectionInstanceOtherThanAnIlluminateOne(): void
    {
        // Arrange
        $editor = new IlluminateEditor(
            $this->prophesize(EditorInterface::class)->reveal()
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $editor->reviseRouting(new SymfonyRouteCollection());
    }

    public function testRefuseReturningARouteCollectionInstanceOtherThanAnIlluminateOne(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(EditorInterface::class);
        $juniorEditor->reviseRouting(Argument::any())
            ->willReturn(new SymfonyRouteCollection());

        $editor = new IlluminateEditor(
            $juniorEditor->reveal()
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $editor->reviseRouting(new RouteCollection());
    }

    public function testDelegateRevisingTheRoutesToTheJuniorEditor(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(EditorInterface::class);
        $juniorEditor->reviseRouting(Argument::any())
            ->willReturn(new RouteCollection());

        $editor = new IlluminateEditor(
            $juniorEditor->reveal()
        );

        // Act
        $editor->reviseRouting($originalRouteCollection = new RouteCollection());

        // Assert
        $juniorEditor->reviseRouting($originalRouteCollection)
            ->shouldHaveBeenCalled();
    }

    public function testJuniorEditorReportsBackTheRevisedRoutes(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(EditorInterface::class);
        $juniorEditor->reviseRouting(Argument::any())
            ->willReturn($juniorRevisedRouteCollection = new RouteCollection());

        $editor = new IlluminateEditor(
            $juniorEditor->reveal()
        );

        // Act
        $revisedRouteCollection = $editor->reviseRouting(new RouteCollection());

        // Assert
        self::assertSame($juniorRevisedRouteCollection, $revisedRouteCollection);
    }

    public function testRefuseReturningARequestInstanceOtherThanAnIlluminateOne(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(EditorInterface::class);
        $juniorEditor->reviseRequest(Argument::any())
            ->willReturn(new SymfonyRequest());

        $editor = new IlluminateEditor(
            $juniorEditor->reveal()
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $editor->reviseRequest(new Request());
    }

    public function testRefuseARequestInstanceOtherThanAnIlluminateOne(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(EditorInterface::class);

        $editor = new IlluminateEditor(
            $juniorEditor->reveal()
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $editor->reviseRequest(new SymfonyRequest());
    }

    public function testDelegateRevisingTheRequestToTheJuniorEditor(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(EditorInterface::class);
        $juniorEditor->reviseRequest(Argument::any())
            ->willReturn(new Request());

        $editor = new IlluminateEditor(
            $juniorEditor->reveal()
        );

        // Act
        $editor->reviseRequest($originalRequest = new Request());

        // Assert
        $juniorEditor->reviseRequest($originalRequest)
            ->shouldHaveBeenCalled();
    }

    public function testJuniorEditorReportsBackTheRevisedRequest(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(EditorInterface::class);
        $juniorEditor->reviseRequest(Argument::any())
            ->willReturn($juniorRevisedRequest = new Request());

        $editor = new IlluminateEditor(
            $juniorEditor->reveal()
        );

        // Act
        $revisedRequest = $editor->reviseRequest(new Request());

        // Assert
        self::assertSame($juniorRevisedRequest, $revisedRequest);
    }

    public function testRefuseAResponseInstanceOtherThanAnIlluminateOne(): void
    {
        // Arrange
        $editor = new IlluminateEditor(
            $this->createMock(EditorInterface::class)
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $editor->reviseResponse(new SymfonyResponse());
    }

    public function testRefuseReturningAResponseInstanceOtherThanAnIlluminateOne(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(EditorInterface::class);
        $juniorEditor->reviseResponse(Argument::any())
            ->willReturn(new SymfonyResponse());

        $editor = new IlluminateEditor(
            $juniorEditor->reveal()
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $editor->reviseResponse(new Response());
    }

    public function testDelegateRevisingTheResponseToTheJuniorEditor(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(EditorInterface::class);
        $juniorEditor->reviseResponse(Argument::any())
            ->willReturn(new Response());

        $editor = new IlluminateEditor(
            $juniorEditor->reveal()
        );

        // Act
        $editor->reviseResponse($originalResponse = new Response());

        // Assert
        $juniorEditor->reviseResponse($originalResponse)
            ->shouldHaveBeenCalled();
    }

    public function testJuniorEditorReportsBackTheRevisedResponse(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(EditorInterface::class);
        $juniorEditor->reviseResponse(Argument::any())
            ->willReturn($juniorRevisedResponse = new Response());

        $editor = new IlluminateEditor(
            $juniorEditor->reveal()
        );

        // Act
        $revisedResponse = $editor->reviseResponse(new Response());

        // Assert
        self::assertSame($juniorRevisedResponse, $revisedResponse);
    }
}