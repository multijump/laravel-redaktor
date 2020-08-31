<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Integration;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;

/**
 * @property-read Application $app
 */
trait InteractsWithApplication
{
    /**
     * @var array
     */
    private $config = [];

    protected function withConfig(array $config): void
    {
        $this->config = $config;
    }

    private function createApplication(array $config): Application
    {
        $app = new Application(__DIR__ . '/../../vendor/laravel/laravel/');

        $app->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            \Illuminate\Foundation\Http\Kernel::class
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
        $this->overrideConfig($app, $config);
        $this->bootstrapServiceProviders($app);

        return $app;
    }

    private function loadPackageServiceProviders(Application $app): void
    {
        /** @var Repository $configRepo */
        $configRepo = $app->make('config');
        foreach ($this->getPackageProviders($app) as $packageProvider) {
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

    private function overrideConfig(Application $app, array $overrides): void
    {
        if ($overrides) {
            $app->make('config')->set($overrides);
        }
    }

    private function bootstrapServiceProviders(Application $app): void
    {
        $app->boot();
    }

    abstract protected function getPackageProviders(Application $app);

    public function __get($name): Application
    {
        if ($name === 'app') {
            return $this->app = $this->createApplication($this->config);
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