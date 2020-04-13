<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Version;

use Illuminate\Http\Request;

final class InvalidRequestException extends \InvalidArgumentException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function make($request): self
    {
        return new self(
            sprintf(
                'Instance of %s expected. Got %s.',
                Request::class,
                is_object($request) ? get_class($request) : gettype($request)
            )
        );
    }
}