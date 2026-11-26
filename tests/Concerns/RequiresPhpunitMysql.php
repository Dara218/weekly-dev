<?php

namespace Tests\Concerns;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

/**
 * Run DB-backed feature tests only against MySQL databases named *_phpunit.
 *
 * Uses DatabaseTransactions for rollback. When phpunit.xml points at sqlite :memory:
 * (no pdo_sqlite), connectionsToTransact() returns [] so parent::setUp() does not
 * open a PDO connection, then setUpRequiresPhpunitMysql() skips the test.
 */
trait RequiresPhpunitMysql
{
    use DatabaseTransactions;

    /**
     * Only begin a transaction when the dedicated phpunit MySQL database is configured.
     *
     * @return list<string|null>
     */
    protected function connectionsToTransact(): array
    {
        return $this->usesPhpunitMysqlDatabase()
            ? [config('database.default')]
            : [];
    }

    /**
     * Skip when not on *_phpunit MySQL, otherwise relax foreign keys for fixtures.
     */
    protected function setUpRequiresPhpunitMysql(): void
    {
        if (! $this->usesPhpunitMysqlDatabase()) {
            self::markTestSkipped(
                'Needs a dedicated MySQL database whose name ends with _phpunit; got '
                . $this->configuredDatabaseName()
                . '.',
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    /**
     * Whether the default connection is MySQL with a *_phpunit database name.
     *
     * Reads config only so this works before a PDO connection exists.
     */
    protected function usesPhpunitMysqlDatabase(): bool
    {
        $connection = (string) config('database.default');
        $driver = (string) config("database.connections.{$connection}.driver");
        $database = (string) config("database.connections.{$connection}.database");

        return $driver === 'mysql' && str_ends_with($database, '_phpunit');
    }

    /**
     * Configured database name for skip messages.
     */
    protected function configuredDatabaseName(): string
    {
        $connection = (string) config('database.default');

        return (string) config("database.connections.{$connection}.database");
    }
}
