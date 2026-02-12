<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Support\Contracts\ClockInterface;
use Support\Contracts\MapperInterface;
use Support\Contracts\TransactionInterface;
use Support\Contracts\UuidGeneratorInterface;
use Support\DebugInfrastructures\NopTransaction;
use Support\DebugInfrastructures\Repository\DebugConfig;
use Support\DebugInfrastructures\Repository\FileStore;
use Support\Infrastructures\Clock;
use Support\Infrastructures\Database\SQLiteConfig;
use Support\Infrastructures\Mapper;
use Support\Infrastructures\StrictMapper;
use Support\Infrastructures\UuidGenerator;

class SupportServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MapperInterface::class, Mapper::class);
        $this->app->when(FileStore::class)
            ->needs(MapperInterface::class)
            ->give(StrictMapper::class);

        $this->app->bind(UuidGeneratorInterface::class, UuidGenerator::class);
        $this->app->bind(TransactionInterface::class, NopTransaction::class);
        $this->app->bind(ClockInterface::class, Clock::class);

        $this->app->bind(
            DebugConfig::class,
            fn (): DebugConfig => new DebugConfig(config()->string('debug.file.path')),
        );

        $this->app->bind(
            SQLiteConfig::class,
            fn (): SQLiteConfig => new SQLiteConfig(
                config()->string('database.connections.sqlite.database'),
                nullableBool('database.connections.sqlite.foreign_key_constraints'),
                nullableInt('database.connections.sqlite.busy_timeout'),
                nullableString('database.connections.sqlite.journal_mode'),
                nullableString('database.connections.sqlite.synchronous'),
            ),
        );
    }
}
