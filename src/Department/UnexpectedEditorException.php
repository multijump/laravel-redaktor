<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Department;

use DSLabs\Redaktor\Editor\EditorInterface;

final class UnexpectedEditorException extends \UnexpectedValueException
{
    public function __construct(string $expectedEditorType, EditorInterface $actualEditor)
    {
        parent::__construct(
            sprintf(
                'Expected an instance of %s. Got %s.',
                $expectedEditorType,
                get_class($actualEditor)
            )
        );
    }
}