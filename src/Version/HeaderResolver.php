<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Version;

use DSLabs\Redaktor\Version\VersionResolver;
use Illuminate\Http\Request;

final class HeaderResolver implements VersionResolver
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function resolve($request): ?string
    {
        if (!$request instanceof Request) {
            throw InvalidRequestException::make($request);
        }

        return $request->header($this->name, null);
    }
}
