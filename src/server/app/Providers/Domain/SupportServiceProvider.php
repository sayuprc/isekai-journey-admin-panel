<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Support\Contracts\ClockInterface;
use Support\Contracts\MapperInterface;
use Support\Contracts\TransactionInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\DebugInfrastructures\Repository\DebugConfig;
use Support\Infrastructures\Clock;
use Support\Infrastructures\Database\SQLiteConfig;
use Support\Infrastructures\DbTransaction;
use Support\Infrastructures\Mapper;
use Support\Infrastructures\Uuid\UuidConverter;
use Support\Infrastructures\Uuid\UuidGenerator;

class SupportServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MapperInterface::class, Mapper::class);
        $this->app->bind(UuidGeneratorInterface::class, UuidGenerator::class);
        $this->app->bind(UuidConverterInterface::class, UuidConverter::class);
        $this->app->bind(TransactionInterface::class, DbTransaction::class);
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
