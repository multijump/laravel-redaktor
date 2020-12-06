<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Department;

use DSLabs\LaravelRedaktor\Editor\IlluminateMessageEditor;
use DSLabs\Redaktor\Department\MessageDepartment;
use DSLabs\Redaktor\Editor\Brief;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Editor\MessageEditorInterface;

final class IlluminateMessageDepartment implements MessageDepartment
{
    /**
     * @var MessageDepartment
     */
    private $messageDepartment;

    public function __construct(MessageDepartment $messageDepartment)
    {
        $this->messageDepartment = $messageDepartment;
    }

    /**
     * @inheritDoc
     *
     * @return IlluminateMessageEditor
     */
    public function provideEditor(Brief $brief): EditorInterface
    {
        $messageEditor = $this->messageDepartment->provideEditor($brief);
        if (!$messageEditor instanceof MessageEditorInterface) {
            self::throwUnexpectedEditorException($messageEditor);
        }

        return new IlluminateMessageEditor($messageEditor);
    }

    private static function throwUnexpectedEditorException(EditorInterface $juniorEditor): void
    {
        throw new UnexpectedEditorException(
            MessageEditorInterface::class,
            $juniorEditor
        );
    }
}
