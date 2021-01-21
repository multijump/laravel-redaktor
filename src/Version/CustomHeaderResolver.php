<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Version;

use DSLabs\Redaktor\Version\Strategy;
use DSLabs\Redaktor\Version\Version;
use Illuminate\Http\Request;

/**
 * Resolve target version from a Request custom header.
 */
final class CustomHeaderResolver implements Strategy
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name Header name to get the target version from.
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
            $request->header($this->name, '')
        );
    }
}
