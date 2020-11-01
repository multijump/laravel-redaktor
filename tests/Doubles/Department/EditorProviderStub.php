<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Doubles\Department;

use DSLabs\Redaktor\Department\EditorProvider;
use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\EditorInterface;

final class EditorProviderStub implements EditorProvider
{
    private $editor;

    public function __construct(EditorInterface $editor)
    {
        $this->editor = $editor;
    }

    public function provideEditor(Brief $brief): EditorInterface
    {
        return $this->editor;
    }
}
