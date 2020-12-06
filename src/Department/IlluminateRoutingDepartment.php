<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Department;

use DSLabs\LaravelRedaktor\Editor\IlluminateRoutingEditor;
use DSLabs\Redaktor\Department\RoutingDepartment;
use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Editor\RoutingEditorInterface;

final class IlluminateRoutingDepartment implements RoutingDepartment
{
    /**
     * @var RoutingDepartment
     */
    private $routingDepartment;

    public function __construct(RoutingDepartment $routingDepartment)
    {
        $this->routingDepartment = $routingDepartment;
    }

    /**
     * @inheritDoc
     *
     * @return IlluminateRoutingEditor
     */
    public function provideEditor(Brief $brief): EditorInterface
    {
        $routingEditor = $this->routingDepartment->provideEditor($brief);
        if (!$routingEditor instanceof RoutingEditorInterface) {
            self::throwUnexpectedEditorException($routingEditor);
        }

        return new IlluminateRoutingEditor($routingEditor);
    }

    private static function throwUnexpectedEditorException(EditorInterface $juniorEditor): void
    {
        throw new UnexpectedEditorException(
            RoutingEditorInterface::class,
            $juniorEditor
        );
    }
}
