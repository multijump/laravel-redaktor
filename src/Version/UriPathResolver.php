<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Version;

use DSLabs\Redaktor\Version\Strategy;
use DSLabs\Redaktor\Version\Version;
use Illuminate\Http\Request;

/**
 * Resolve target version from the Request URI path.
 */
final class UriPathResolver implements Strategy
{
    /**
     * @var int
     */
    private $index;

    /**
     * @param int $index Index (0-based) of the path segment to get the target version from.
     */
    public function __construct(int $index)
    {
        $this->index = $index;
    }

    /**
     * @param Request $request
     */
    public function resolve(object $request): Version
    {
        if (!$request instanceof Request) {
            throw InvalidRequestException::make($request);
        }

        $segments = explode('/', $request->decodedPath());

        return new Version($segments[$this->index]);
    }
}
