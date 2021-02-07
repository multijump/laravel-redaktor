<?php

namespace DSLabs\LaravelRedaktor\Version;

use DSLabs\LaravelRedaktor\Guard\IlluminateGuard;
use DSLabs\Redaktor\Version\Strategy;
use DSLabs\Redaktor\Version\UnresolvedVersionException;
use DSLabs\Redaktor\Version\Version;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class DatabaseStrategy implements Strategy
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $column;

    /**
     * @var callable|null
     */
    private $filter;

    /**
     * @var Builder
     */
    private $queryBuilder;

    public function __construct(
        string $table = 'redaktor',
        string $column = 'version',
        callable $filter = null
    ) {
        $this->table = $table;
        $this->column = $column;
        $this->filter = $filter ?? self::defaultFilter();
    }

    public function withQueryBuilder(Builder $queryBuilder): void
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param Request $request
     *
     * @return Version
     */
    public function resolve(object $request): Version
    {
        if (!$this->queryBuilder) {
            throw new \LogicException(
                'Unset $queryBuilder class property. Call `setQueryBuilder()` before calling the `resolve()` method.'
            );
        }

        IlluminateGuard::assertRequest($request);

        $version = $this->queryBuilder
            ->newQuery()
            ->select($this->column)
            ->from($this->table)
            ->where(
                $this->resolveFilter($request)
            )
            ->value($this->column);

        if (!$version) {
            throw new UnresolvedVersionException($request);
        }

        return new Version($version);
    }

    private function resolveFilter(object $request): array
    {
        return call_user_func($this->filter, $request);
    }

    private static function defaultFilter(): callable
    {
        return static function (Request $request): array {
            return [
                'app_id' => $request->header('Application-Id'),
            ];
        };
    }
}
