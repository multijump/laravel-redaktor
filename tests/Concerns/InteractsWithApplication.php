<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Concerns;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

/**
 * @property-read Application $app
 */
trait InteractsWithApplication
{
    /**
     * Provides a list of Service Providers to be registered.
     */
    abstract protected function getServiceProviders(Application $app): array;

    /**
     * Provides the Application instance
     *
     * @return Application
     */
    protected function getApplication(): Application
    {
        return $this->app;
    }

    protected function getKernel(): Kernel
    {
        return $this->app->make(Kernel::class);
    }

    private function createApplication(): Application
    {
        /** @var Application $app */
        $app = require __DIR__.'/../../vendor/laravel/laravel/bootstrap/app.php';

        $app->instance('request', new Request());
        $app->make(Kernel::class)->bootstrap();

        foreach ($this->getServiceProviders($app) as $serviceProvider) {
            $app->register($serviceProvider);
        }

        return $app;
    }

    public function __get($name): Application
    {
        if ($name === 'app') {
            return $this->app = $this->createApplication();
        }

        throw new \ErrorException(
            sprintf(
                "Undefined property: %s::%s",
                __CLASS__,
                "\${$name}"
            )
        );
    }
}