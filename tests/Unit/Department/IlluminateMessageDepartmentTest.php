<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Unit\Department;

use DSLabs\LaravelRedaktor\Department\IlluminateMessageDepartment;
use DSLabs\LaravelRedaktor\Department\UnexpectedEditorException;
use DSLabs\LaravelRedaktor\Editor\IlluminateMessageEditor;
use DSLabs\LaravelRedaktor\Tests\Doubles\Department\EditorProviderStub;
use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Editor\MessageEditorInterface;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Version\Version;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @see IlluminateMessageDepartment
 */
final class IlluminateMessageDepartmentTest extends TestCase
{
    use ProphecyTrait;

    public function testProvidesAnIlluminateMessageEditor(): void
    {
        // Arrange
        $juniorMessageEditor = $this->prophesize(MessageEditorInterface::class)
            ->reveal();

        $illuminateMessageDepartment = new IlluminateMessageDepartment(
            new EditorProviderStub($juniorMessageEditor)
        );

        // Act
        $messageEditor = $illuminateMessageDepartment->provideEditor(self::createBrief());

        // Assert
        self::assertInstanceOf(IlluminateMessageEditor::class, $messageEditor);
    }

    public function testRefusesNonMessageJuniorEditors(): void
    {
        // Arrange
        $juniorGenericEditor = $this->prophesize(EditorInterface::class)->reveal();

        $illuminateMessageDepartment = new IlluminateMessageDepartment(
            new EditorProviderStub($juniorGenericEditor)
        );

        // Assert
        $this->expectException(UnexpectedEditorException::class);

        // Act
        $illuminateMessageDepartment->provideEditor(self::createBrief());
    }

    public function testDelegatesRetrievingTheBriefedVersionToTheJuniorMessageEditor(): void
    {
        // Arrange
        $juniorMessageEditorProphecy = $this->prophesize(MessageEditorInterface::class);
        $juniorMessageEditorProphecy->briefedVersion()
            ->willReturn($expectedVersion = new Version('foo'));

        $illuminateMessageDepartment = new IlluminateMessageDepartment(
            new EditorProviderStub($juniorMessageEditorProphecy->reveal())
        );
        $illuminateMessageEditor = $illuminateMessageDepartment->provideEditor(self::createBrief());

        // Act / Assert
        self::assertSame(
            $expectedVersion,
            $illuminateMessageEditor->briefedVersion()
        );
    }

    public function testDelegatesRetrievingTheBriefedRevisionsToTheJuniorMessageEditor(): void
    {
        // Arrange
        $juniorMessageEditorProphecy = $this->prophesize(MessageEditorInterface::class);
        $juniorMessageEditorProphecy->briefedRevisions()
            ->willReturn($expectedRevisions = [
                new class() implements Revision {
                },
            ]);

        $illuminateMessageDepartment = new IlluminateMessageDepartment(
            new EditorProviderStub($juniorMessageEditorProphecy->reveal())
        );
        $editor = $illuminateMessageDepartment->provideEditor(self::createBrief());

        // Act / Assert
        self::assertSame(
            $expectedRevisions,
            $editor->briefedRevisions()
        );
    }

    private static function createBrief(): Brief
    {
        return new Brief(
            new Version('foo'),
            []
        );
    }
}
