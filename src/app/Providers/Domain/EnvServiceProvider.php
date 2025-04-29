<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Support\Application\Config\Config;
use Support\Mapper\MapperInterface;

abstract class EnvServiceProvider extends ServiceProvider
{
    protected function isMock(): bool
    {
        return strtolower($this->app->make(Config::class)->getString('app.env')) === 'mock';
    }

    protected function getMapper(): MapperInterface
    {
        return $this->app->make(MapperInterface::class);
    }
}
