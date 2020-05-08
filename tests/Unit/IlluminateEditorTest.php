<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit;

use DSLabs\LaravelRedaktor\IlluminateEditor;
use DSLabs\Redaktor\Editor\EditorInterface;
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
        $juniorEditorMock = $this->prophesize(EditorInterface::class);
        $juniorEditorMock->reviseRouting(Argument::any())
            ->willReturn(new SymfonyRouteCollection());

        $editor = new IlluminateEditor(
            $juniorEditorMock->reveal()
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $editor->reviseRouting(new RouteCollection());
    }

    public function testDelegateRevisingTheRoutesToTheAssistantEditor(): void
    {
        // Arrange
        $assistantEditorSpy = $this->prophesize(EditorInterface::class);
        $assistantEditorSpy->reviseRouting(Argument::any())
            ->willReturn(new RouteCollection());

        $editor = new IlluminateEditor(
            $assistantEditorSpy->reveal()
        );

        // Act
        $editor->reviseRouting($originalRouteCollection = new RouteCollection());

        // Assert
        $assistantEditorSpy->reviseRouting($originalRouteCollection)
            ->shouldHaveBeenCalled();
    }

    public function testAssistantEditorReportsBackTheRevisedRoutes(): void
    {
        // Arrange
        $assistantEditorSpy = $this->prophesize(EditorInterface::class);
        $assistantEditorSpy->reviseRouting(Argument::any())
            ->willReturn($assistantRevisedRouteCollection = new RouteCollection());

        $editor = new IlluminateEditor(
            $assistantEditorSpy->reveal()
        );

        // Act
        $revisedRouteCollection = $editor->reviseRouting(new RouteCollection());

        // Assert
        self::assertSame($assistantRevisedRouteCollection, $revisedRouteCollection);
    }

    public function testRefuseReturningARequestInstanceOtherThanAnIlluminateOne(): void
    {
        // Arrange
        $assistantEditorSpy = $this->prophesize(EditorInterface::class);
        $assistantEditorSpy->reviseRequest()
            ->willReturn(new SymfonyRequest());

        $editor = new IlluminateEditor(
            $assistantEditorSpy->reveal()
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $editor->reviseRequest();
    }

    public function testDelegateRevisingTheRequestToTheAssistantEditor(): void
    {
        // Arrange
        $assistantEditorSpy = $this->prophesize(EditorInterface::class);
        $assistantEditorSpy->reviseRequest()
            ->willReturn(new Request());

        $editor = new IlluminateEditor(
            $assistantEditorSpy->reveal()
        );

        // Act
        $editor->reviseRequest();

        // Assert
        $assistantEditorSpy->reviseRequest()
            ->shouldHaveBeenCalled();
    }

    public function testAssistantEditorReportsBackTheRevisedRequest(): void
    {
        // Arrange
        $assistantEditorSpy = $this->prophesize(EditorInterface::class);
        $assistantEditorSpy->reviseRequest()
            ->willReturn($assistantRevisedRequest = new Request());

        $editor = new IlluminateEditor(
            $assistantEditorSpy->reveal()
        );

        // Act
        $revisedRequest = $editor->reviseRequest();

        // Assert
        self::assertSame($assistantRevisedRequest, $revisedRequest);
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
        $assistantEditorMock = $this->prophesize(EditorInterface::class);
        $assistantEditorMock->reviseResponse(Argument::any())
            ->willReturn(new SymfonyResponse());

        $editor = new IlluminateEditor(
            $assistantEditorMock->reveal()
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $editor->reviseResponse(new Response());
    }

    public function testDelegateRevisingTheResponseToTheAssistantEditor(): void
    {
        // Arrange
        $assistantEditorSpy = $this->prophesize(EditorInterface::class);
        $assistantEditorSpy->reviseResponse(Argument::any())
            ->willReturn(new Response());

        $editor = new IlluminateEditor(
            $assistantEditorSpy->reveal()
        );

        // Act
        $editor->reviseResponse($originalResponse = new Response());

        // Assert
        $assistantEditorSpy->reviseResponse($originalResponse)
            ->shouldHaveBeenCalled();
    }

    public function testAssistantEditorReportsBackTheRevisedResponse(): void
    {
        // Arrange
        $assistantEditorSpy = $this->prophesize(EditorInterface::class);
        $assistantEditorSpy->reviseResponse(Argument::any())
            ->willReturn($assistantRevisedResponse = new Response());

        $editor = new IlluminateEditor(
            $assistantEditorSpy->reveal()
        );

        // Act
        $revisedResponse = $editor->reviseResponse(new Response());

        // Assert
        self::assertSame($assistantRevisedResponse, $revisedResponse);
    }
}