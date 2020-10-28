<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Integration\Department;

use DSLabs\LaravelRedaktor\Department\IlluminateDepartment;
use DSLabs\LaravelRedaktor\IlluminateEditor;
use DSLabs\Redaktor\Department\EditorDepartment;
use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Version\Version;
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
            $version = new Version('foo'),
            $revisions = [
                self::createDummyRevision(),
            ]
        );

        // Act
        $editor = $illuminateDepartment->provideEditor($brief);

        // Assert
        self::assertInstanceOf(IlluminateEditor::class, $editor);
        self::assertSame($version, $editor->briefedVersion());
        self::assertSame($revisions, $editor->briefedRevisions());
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