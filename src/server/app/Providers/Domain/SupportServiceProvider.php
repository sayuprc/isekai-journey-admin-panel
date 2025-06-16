<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Support\Application\Config;
use Support\Application\Mapper;
use Support\Application\Uuid\DummyUuidGenerator;
use Support\Application\Uuid\UuidGenerator;
use Support\Contracts\ConfigInterface;
use Support\Contracts\MapperInterface;
use Support\Contracts\UuidGeneratorInterface;

class SupportServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ConfigInterface::class, Config::class);
        $this->app->bind(MapperInterface::class, Mapper::class);

        if ($this->isMock()) {
            $this->app->bind(UuidGeneratorInterface::class, UuidGenerator::class);
        } else {
            $this->app->bind(UuidGeneratorInterface::class, DummyUuidGenerator::class);
        }
    }
}
