<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Department;

use DSLabs\LaravelRedaktor\Editor\IlluminateRoutingEditor;
use DSLabs\Redaktor\Department\EditorProvider;
use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Editor\RoutingEditorInterface;

final class IlluminateRoutingDepartment implements EditorProvider
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
     * @return IlluminateRoutingEditor
     */
    public function provideEditor(Brief $brief): EditorInterface
    {
        $juniorEditor = $this->juniorEditorProvider->provideEditor($brief);
        if (!$juniorEditor instanceof RoutingEditorInterface) {
            self::throwUnexpectedEditorException($juniorEditor);
        }

        return new IlluminateRoutingEditor($juniorEditor);
    }

    private static function throwUnexpectedEditorException(EditorInterface $juniorEditor): void
    {
        throw new UnexpectedEditorException(
            RoutingEditorInterface::class,
            $juniorEditor
        );
    }
}