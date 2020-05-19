<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Department;

use DSLabs\LaravelRedaktor\Guard\IlluminateGuard;
use DSLabs\LaravelRedaktor\IlluminateEditor;
use DSLabs\Redaktor\Department\EditorProvider;
use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\EditorInterface;

final class IlluminateDepartment implements EditorProvider
{
    /**
     * @var EditorProvider
     */
    private $editorProvider;

    public function __construct(EditorProvider $editorProvider)
    {
        $this->editorProvider = $editorProvider;
    }

    /**
     * @inheritDoc
     *
     * @return IlluminateEditor
     */
    public function provideEditor(Brief $brief): EditorInterface
    {
        IlluminateGuard::assertRequest($brief->request());

        return new IlluminateEditor(
            $this->editorProvider->provideEditor($brief)
        );
    }
}