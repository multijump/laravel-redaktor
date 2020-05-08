<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit;

use DSLabs\LaravelRedaktor\IlluminateChiefEditor;
use DSLabs\LaravelRedaktor\IlluminateEditor;
use DSLabs\Redaktor\ChiefEditorInterface;
use DSLabs\Redaktor\Editor\EditorInterface;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * @see IlluminateChiefEditor
 */
final class IlluminateChiefEditorTest extends TestCase
{
    public function testDelegatesAppointingAnEditorToTheDeputyChiefEditor(): void
    {
        // Arrange
        $deputyChiefEditor = $this->prophesize(ChiefEditorInterface::class);
        $deputyChiefEditor->appointEditor(Argument::any())
            ->willReturn(
                $this->prophesize(EditorInterface::class)->reveal()
            );

        $chiefEditor = new IlluminateChiefEditor(
            $deputyChiefEditor->reveal()
        );

        // Act
        $chiefEditor->appointEditor($request = new Request());

        // Assert
        $deputyChiefEditor->appointEditor($request)
            ->shouldHaveBeenCalled();
    }

    public function testDeputyChiefEditorReportsBackTheAppointedEditor(): void
    {
        // Arrange
        $deputyChiefEditor = $this->prophesize(ChiefEditorInterface::class);
        $deputyChiefEditor->appointEditor(Argument::any())
            ->willReturn(
                $this->prophesize(EditorInterface::class)->reveal()
            );

        $chiefEditor = new IlluminateChiefEditor(
            $deputyChiefEditor->reveal()
        );

        // Act
        $editor = $chiefEditor->appointEditor(new Request());

        // Assert
        self::assertInstanceOf(IlluminateEditor::class, $editor);
    }

    public function testRefuseARequestInstanceOtherThanAnIlluminateOne(): void
    {
        // Arrange
        $chiefEditor = new IlluminateChiefEditor(
            $this->prophesize(ChiefEditorInterface::class)->reveal()
        );

        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        $chiefEditor->appointEditor(new SymfonyRequest());
    }
}