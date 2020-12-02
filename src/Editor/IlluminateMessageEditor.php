<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Editor;

use DSLabs\LaravelRedaktor\Guard\IlluminateGuard;
use DSLabs\Redaktor\Editor\EditorInterface;
use DSLabs\Redaktor\Editor\MessageEditorInterface;
use DSLabs\Redaktor\Revision\Revision;
use DSLabs\Redaktor\Version\Version;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class IlluminateMessageEditor implements MessageEditorInterface
{
    /**
     * @var EditorInterface
     */
    private $juniorMessageEditor;

    public function __construct(MessageEditorInterface $juniorMessageEditor)
    {
        $this->juniorMessageEditor = $juniorMessageEditor;
    }

    /**
     * @inheritDoc
     */
    public function briefedVersion(): Version
    {
        return $this->juniorMessageEditor->briefedVersion();
    }

    /**
     * @inheritDoc
     *
     * @return Revision[]
     */
    public function briefedRevisions(): array
    {
        return $this->juniorMessageEditor->briefedRevisions();
    }

    /**
     * @inheritDoc
     *
     * @return Request
     */
    public function reviseRequest(object $request): object
    {
        IlluminateGuard::assertRequest($request);

        $revisedRequest = $this->juniorMessageEditor->reviseRequest($request);

        IlluminateGuard::assertRequest($revisedRequest);

        return $revisedRequest;
    }

    /**
     * @inheritDoc
     *
     * @param Response $response
     *
     * @return Response
     */
    public function reviseResponse(object $response): object
    {
        IlluminateGuard::assertResponse($response);

        $revisedResponse = $this->juniorMessageEditor->reviseResponse($response);

        IlluminateGuard::assertResponse($revisedResponse);

        return $revisedResponse;
    }
}
