<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit\Editor;

use DSLabs\LaravelRedaktor\Editor\IlluminateMessageEditor;
use DSLabs\Redaktor\Editor\MessageEditorInterface;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Version\Version;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * @see IlluminateMessageEditor
 */
final class IlluminateMessageEditorTest extends TestCase
{
    use ProphecyTrait;

    public function testRetrievesTheBriefedVersion(): void
    {
        // Arrange
        $juniorEditor = $this->createMessageEditorProphecy();
        $juniorEditor->briefedVersion()
            ->willReturn($expectedVersion = new Version('foo'));
        $illuminateEditor = new IlluminateMessageEditor($juniorEditor->reveal());

        // Act
        $briefedVersion = $illuminateEditor->briefedVersion();

        // Assert
        self::assertSame($expectedVersion, $briefedVersion);
    }

    public function testRetrievesTheBriefedRevisions(): void
    {
        // Arrange
        $juniorEditor = $this->createMessageEditorProphecy();
        $juniorEditor->briefedRevisions()
            ->willReturn($expectedRevisions = [
                new class() implements Revision {
                },
                new class() implements Revision {
                },
            ]);
        $illuminateEditor = new IlluminateMessageEditor($juniorEditor->reveal());

        // Act
        $briefedRevisions = $illuminateEditor->briefedRevisions();

        // Assert
        self::assertSame($expectedRevisions, $briefedRevisions);
    }

    public function testRefuseReturningARequestInstanceOtherThanAnIlluminateOne(): void
    {
        // Arrange
        $juniorEditor = $this->createMessageEditorProphecy();
        $juniorEditor->reviseRequest(Argument::any())
            ->willReturn(new SymfonyRequest());
        $illuminateEditor = new IlluminateMessageEditor($juniorEditor->reveal());

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $illuminateEditor->reviseRequest(new Request());
    }

    public function testRefuseARequestInstanceOtherThanAnIlluminateOne(): void
    {
        // Arrange
        $juniorEditor = $this->createMessageEditorProphecy();
        $illuminateEditor = new IlluminateMessageEditor($juniorEditor->reveal());

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $illuminateEditor->reviseRequest(new SymfonyRequest());
    }

    public function testDelegateRevisingTheRequestToTheJuniorEditor(): void
    {
        // Arrange
        $juniorEditor = $this->createMessageEditorProphecy();
        $juniorEditor->reviseRequest(Argument::any())
            ->willReturn(new Request());
        $illuminateEditor = new IlluminateMessageEditor($juniorEditor->reveal());

        // Act
        $illuminateEditor->reviseRequest($originalRequest = new Request());

        // Assert
        $juniorEditor->reviseRequest($originalRequest)
            ->shouldHaveBeenCalled();
    }

    public function testJuniorEditorReportsBackTheRevisedRequest(): void
    {
        // Arrange
        $juniorEditor = $this->createMessageEditorProphecy();
        $juniorEditor->reviseRequest(Argument::any())
            ->willReturn($juniorRevisedRequest = new Request());
        $illuminateEditor = new IlluminateMessageEditor($juniorEditor->reveal());

        // Act
        $revisedRequest = $illuminateEditor->reviseRequest(new Request());

        // Assert
        self::assertSame($juniorRevisedRequest, $revisedRequest);
    }

    public function testRefuseAResponseInstanceOtherThanAnIlluminateOne(): void
    {
        // Arrange
        $juniorEditor = $this->createMock(MessageEditorInterface::class);
        $illuminateEditor = new IlluminateMessageEditor($juniorEditor);

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $illuminateEditor->reviseResponse(new SymfonyResponse());
    }

    public function testRefuseReturningAResponseInstanceOtherThanAnIlluminateOne(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(MessageEditorInterface::class);
        $juniorEditor->reviseResponse(Argument::any())
            ->willReturn(new SymfonyResponse());
        $illuminateEditor = new IlluminateMessageEditor($juniorEditor->reveal());

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $illuminateEditor->reviseResponse(new Response());
    }

    public function testDelegateRevisingTheResponseToTheJuniorEditor(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(MessageEditorInterface::class);
        $juniorEditor->reviseResponse(Argument::any())
            ->willReturn(new Response());
        $illuminateEditor = new IlluminateMessageEditor($juniorEditor->reveal());

        // Act
        $illuminateEditor->reviseResponse($originalResponse = new Response());

        // Assert
        $juniorEditor->reviseResponse($originalResponse)
            ->shouldHaveBeenCalled();
    }

    public function testJuniorEditorReportsBackTheRevisedResponse(): void
    {
        // Arrange
        $juniorEditor = $this->prophesize(MessageEditorInterface::class);
        $juniorEditor->reviseResponse(Argument::any())
            ->willReturn($juniorRevisedResponse = new Response());
        $illuminateEditor = new IlluminateMessageEditor($juniorEditor->reveal());

        // Act
        $revisedResponse = $illuminateEditor->reviseResponse(new Response());

        // Assert
        self::assertSame($juniorRevisedResponse, $revisedResponse);
    }

    /**
     * @return MessageEditorInterface|ObjectProphecy
     */
    private function createMessageEditorProphecy(): ObjectProphecy
    {
        return $this->prophesize(MessageEditorInterface::class);
    }
}
