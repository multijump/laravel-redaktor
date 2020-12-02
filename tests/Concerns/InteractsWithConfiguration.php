<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Concerns;

use Illuminate\Contracts\Foundation\Application;

trait InteractsWithConfiguration
{
    /**
     * Provides the Application instance.
     *
     * @return Application
     */
    abstract protected function getApplication(): Application;

    /**
     * Set a given configuration value.
     *
     * @param array|string $key
     * @param mixed $value
     */
    protected function withConfig($key, $value = null): void
    {
        $this->getApplication()->make('config')->set($key, $value);
    }
}
