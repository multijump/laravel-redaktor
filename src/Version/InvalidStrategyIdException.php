<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Version;

use DSLabs\Redaktor\Version\Strategy;

final class InvalidStrategyIdException extends \RuntimeException
{
    public function __construct(string $strategyId)
    {
        parent::__construct(
            sprintf(
                '%s does not resolve to an instance of %s.',
                $strategyId,
                Strategy::class
            )
        );
    }
}
