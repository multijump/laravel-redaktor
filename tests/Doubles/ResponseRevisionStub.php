<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Doubles;

use DSLabs\Redaktor\Revision\ResponseRevision;

final class ResponseRevisionStub implements ResponseRevision
{
    /**
     * @var object
     */
    private $revisedResponse;

    /**
     * @var bool
     */
    private $isApplicable;

    public function __construct(
        object $revisedResponse,
        bool $isApplicable = true
    ) {
        $this->revisedResponse = $revisedResponse;
        $this->isApplicable = $isApplicable;
    }

    public function isApplicable(object $request): bool
    {
        return $this->isApplicable;
    }

    public function applyToResponse(object $request, object $response): object
    {
        return $this->revisedResponse;
    }
}