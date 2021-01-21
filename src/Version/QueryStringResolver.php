<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Version;

use DSLabs\Redaktor\Version\Strategy;
use DSLabs\Redaktor\Version\Version;
use Illuminate\Http\Request;

/**
 * Resolve target version from the Request query string.
 */
final class QueryStringResolver implements Strategy
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name Name of the query string parameter to get the target version from.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param Request $request
     */
    public function resolve(object $request): Version
    {
        if (!$request instanceof Request) {
            throw InvalidRequestException::make($request);
        }

        return new Version(
            $request->query($this->name, '')
        );
    }
}
