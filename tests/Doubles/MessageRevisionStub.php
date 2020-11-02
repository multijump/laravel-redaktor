<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Doubles;

use DSLabs\Redaktor\Revision\MessageRevision;

final class MessageRevisionStub implements MessageRevision
{
    /**
     * @var object
     */
    private $revisedRequest;

    /**
     * @var object
     */
    private $revisedResponse;

    /**
     * @var bool
     */
    private $isApplicable;

    public function __construct(
        object $revisedRequest = null,
        object $revisedResponse = null,
        bool $isApplicable = true
    ) {
        $this->revisedRequest = $revisedRequest;
        $this->revisedResponse = $revisedResponse;
        $this->isApplicable = $isApplicable;
    }

    public function isApplicable(object $request): bool
    {
        return $this->isApplicable;
    }

    public function applyToRequest(object $request): object
    {
        return $this->revisedRequest;
    }

    public function applyToResponse(object $request, object $response): object
    {
        return $this->revisedResponse;
    }
}
