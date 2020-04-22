<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor;

use DSLabs\LaravelRedaktor\Guard\IlluminateGuard;
use DSLabs\Redaktor\ChiefEditorInterface;
use DSLabs\Redaktor\EditorInterface;
use Illuminate\Http\Request;

final class IlluminateChiefEditor implements ChiefEditorInterface
{
    /**
     * @var ChiefEditorInterface
     */
    private $chiefEditor;

    public function __construct(ChiefEditorInterface $chiefEditor)
    {
        $this->chiefEditor = $chiefEditor;
    }

    /**
     * @param Request $request
     * @return IlluminateEditor
     */
    public function appointEditor(object $request): EditorInterface
    {
        IlluminateGuard::assertRequest($request);

        return new IlluminateEditor(
            $this->chiefEditor->appointEditor($request)
        );
    }
}