<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Concerns;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;

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
        $app = new \Illuminate\Foundation\Application(__DIR__ . '/../../vendor/laravel/laravel/');

        $app->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            \App\Http\Kernel::class
        );

        $app->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            \App\Console\Kernel::class
        );

        $app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Illuminate\Foundation\Exceptions\Handler::class
        );

        $app->make(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class)
            ->bootstrap($app);

        $app->detectEnvironment(static function () {
            return 'test';
        });

        $this->loadPackageServiceProviders($app);
        $this->setupApplication($app);
        $this->bootstrapServiceProviders($app);

        return $app;
    }

    private function loadPackageServiceProviders(Application $app): void
    {
        /** @var Repository $configRepo */
        $configRepo = $app->make('config');
        foreach ($this->getServiceProviders($app) as $packageProvider) {
            $configRepo->push('app.providers', $packageProvider);
        }
    }

    private function setupApplication(Application $app): void
    {
        $app->bootstrapWith([
            \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
            \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
            \Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
            \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        ]);
    }

    private function bootstrapServiceProviders(Application $app): void
    {
        $app->boot();
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