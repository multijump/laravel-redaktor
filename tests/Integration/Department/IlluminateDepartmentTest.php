<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Integration\Department;

use DSLabs\LaravelRedaktor\Department\IlluminateDepartment;
use DSLabs\LaravelRedaktor\IlluminateEditor;
use DSLabs\Redaktor\Department\EditorDepartment;
use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Revision\Revision;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

/**
 * @see IlluminateDepartment
 */
final class IlluminateDepartmentTest extends TestCase
{
    public function testProvideIlluminateEditor(): void
    {
        // Arrange
        $illuminateDepartment = self::createIlluminateDepartment();
        $brief = new Brief(
            $request = new Request(),
            $revisions = [
                self::createDummyRevision(),
            ]
        );

        // Act
        $editor = $illuminateDepartment->provideEditor($brief);

        // Assert
        self::assertInstanceOf(IlluminateEditor::class, $editor);
        self::assertSame($request, $editor->retrieveBriefedRequest());
        self::assertSame($revisions, $editor->retrieveBriefedRevisions());
    }

    public function testRefuseToProvideAnEditorIfBriefDoesNotContainAnIlluminateRequest(): void
    {
        // Arrange
        $illuminateDepartment = self::createIlluminateDepartment();
        $brief = new Brief(
            new \stdClass(),
            []
        );

        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        $illuminateDepartment->provideEditor($brief);
    }

    private static function createIlluminateDepartment(): IlluminateDepartment
    {
        return new IlluminateDepartment(
            new EditorDepartment()
        );
    }

    private static function createDummyRevision(): Revision
    {
        return new class implements Revision {};
    }

}