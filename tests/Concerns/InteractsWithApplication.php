<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Concerns;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

trait InteractsWithApplication
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * Provides a list of Service Providers to be registered.
     */
    abstract protected function getServiceProviders(Application $app): array;

    /**
     * Setup the application and assign it to a class property so it can be
     * accessed from other concerns and the test itself.
     */
    protected function setUp(): void
    {
        if ($this instanceof TestCase) {
            parent::setUp();
        }

        $this->app = $this->createApplication();
        ResourcePublisher::observe($this->app->get('events'));
    }

    /**
     * Clean up so next test runs.
     */
    protected function tearDown(): void
    {
        if ($this->app instanceof Application) {
            ResourcePublisher::revert();

            $this->app->flush();
        }
        $this->app = null;
    }

    /**
     * Provides the Application instance.
     */
    protected function getApplication(): Application
    {
        return $this->app;
    }

    /**
     * Provides the Http Kernel instance.
     */
    protected function getKernel(): Kernel
    {
        return $this->app->make(Kernel::class);
    }

    /**
     * Creates and bootstraps an Application instance.
     */
    private function createApplication(): Application
    {
        /** @var Application $app */
        $app = require __DIR__ . '/../../vendor/laravel/laravel/bootstrap/app.php';

        $app->instance('request', new Request());
        $app->make(Kernel::class)->bootstrap();

        foreach ($this->getServiceProviders($app) as $serviceProvider) {
            $app->register($serviceProvider);
        }

        return $app;
    }
}
