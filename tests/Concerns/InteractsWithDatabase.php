<?php

declare(strict_types=1);

namespace DSLabs\LaravelRedaktor\Tests\Concerns;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Query\Builder;

trait InteractsWithDatabase
{
    abstract public function getApplication(): Application;

    private function getQueryBuilder(): Builder
    {
        return $this->getApplication()->get(Builder::class);
    }

    private function insertInto(string $table, array $values): void
    {
        $this->getQueryBuilder()
            ->from($table)
            ->insert($values);
    }

    private function pinVersion(string $version, string $appId): void
    {
        $this->insertInto(
            'redaktor',
            [
                'version' => $version,
                'app_id' => $appId,
            ]
        );
    }
}
