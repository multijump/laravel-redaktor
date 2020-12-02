<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Department;

use DSLabs\LaravelRedaktor\Editor\IlluminateMessageEditor;
use DSLabs\Redaktor\Department\EditorProvider;
use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Editor\MessageEditorInterface;

final class IlluminateMessageDepartment implements EditorProvider
{
    /**
     * @var EditorProvider
     */
    private $juniorEditorProvider;

    public function __construct(EditorProvider $juniorEditorProvider)
    {
        $this->juniorEditorProvider = $juniorEditorProvider;
    }

    /**
     * @inheritDoc
     *
     * @return IlluminateMessageEditor
     */
    public function provideEditor(Brief $brief): EditorInterface
    {
        $juniorEditor = $this->juniorEditorProvider->provideEditor($brief);
        if (!$juniorEditor instanceof MessageEditorInterface) {
            self::throwUnexpectedEditorException($juniorEditor);
        }

        return new IlluminateMessageEditor($juniorEditor);
    }

    private static function throwUnexpectedEditorException(EditorInterface $juniorEditor): void
    {
        throw new UnexpectedEditorException(
            MessageEditorInterface::class,
            $juniorEditor
        );
    }
}
