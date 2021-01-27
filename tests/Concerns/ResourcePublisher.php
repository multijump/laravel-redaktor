<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Concerns;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

final class ResourcePublisher
{
    private static $publishedPaths = [];

    private function __construct()
    {
        // Prevent instantiation.
    }

    public static function observe(Dispatcher $events): void
    {
        $events->listen(CommandStarting::class, static function (CommandStarting $event) {
            if (!self::isPublishCommand($event)) {
                return;
            }

            $provider = $event->input->getParameterOption('--provider', null);
            $publishedPaths = self::getPublishedPaths($provider);
            self::rememberPaths($publishedPaths);
        });
    }

    public static function revert(): void
    {
        foreach (self::$publishedPaths as $publishedPath) {
            self::deletePath($publishedPath);
        }
    }

    private static function isPublishCommand(CommandStarting $event): bool
    {
        return $event->command === 'vendor:publish';
    }

    /**
     * Returns the list of paths that are been published.
     *
     * If a $provider is passed in, the array returned will only contain the
     * paths published by the given provider; otherwise, the paths for all
     * providers will be returned.
     */
    private static function getPublishedPaths(?string $provider): array
    {
        return $provider
            ? array_values(ServiceProvider::$publishes[$provider])
            : array_merge(...array_map('array_values', array_values(ServiceProvider::$publishes)));
    }

    private static function rememberPaths(array $paths): void
    {
        self::$publishedPaths = array_merge(
            self::$publishedPaths,
            $paths
        );
    }

    private static function deletePath(string $path): void
    {
        if (file_exists($path)) {
            is_file($path)
                ? @unlink($path)
                : rmdir($path);
        }
    }
}
