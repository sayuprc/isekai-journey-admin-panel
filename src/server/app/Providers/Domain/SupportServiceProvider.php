<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Support\Application\Config;
use Support\Application\Mapper;
use Support\Application\UuidGenerator;
use Support\Contracts\ConfigInterface;
use Support\Contracts\MapperInterface;
use Support\Contracts\TransactionInterface;
use Support\Contracts\UuidGeneratorInterface;
use Support\DebugInfrastructures\NopTransaction;

class SupportServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ConfigInterface::class, Config::class);
        $this->app->bind(MapperInterface::class, Mapper::class);
        $this->app->bind(UuidGeneratorInterface::class, UuidGenerator::class);
        $this->app->bind(TransactionInterface::class, NopTransaction::class);
    }
}
