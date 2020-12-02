<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Doubles;

use DSLabs\Redaktor\Revision\RequestRevision;

final class RequestRevisionStub implements RequestRevision
{
    /**
     * @var object
     */
    private $revisedRequest;

    /**
     * @var bool
     */
    private $isApplicable;

    public function __construct(
        object $revisedRequest,
        bool $isApplicable = true
    ) {
        $this->revisedRequest = $revisedRequest;
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
}
