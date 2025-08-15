<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Support\Contracts\ClockInterface;
use Support\Contracts\ConfigInterface;
use Support\Contracts\MapperInterface;
use Support\Contracts\TransactionInterface;
use Support\Contracts\UuidGeneratorInterface;
use Support\DebugInfrastructures\NopTransaction;
use Support\Infrastructures\Clock;
use Support\Infrastructures\Config\Config;
use Support\Infrastructures\Mapper;
use Support\Infrastructures\UuidGenerator;

class SupportServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ConfigInterface::class, Config::class);
        $this->app->bind(MapperInterface::class, Mapper::class);
        $this->app->bind(UuidGeneratorInterface::class, UuidGenerator::class);
        $this->app->bind(TransactionInterface::class, NopTransaction::class);
        $this->app->bind(ClockInterface::class, Clock::class);
    }
}
