<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Doubles;

use DSLabs\Redaktor\Version\Strategy;
use DSLabs\Redaktor\Version\Version;

final class DummyStrategy implements Strategy
{
    public function resolve(object $request): Version
    {
        return new Version('');
    }
}
