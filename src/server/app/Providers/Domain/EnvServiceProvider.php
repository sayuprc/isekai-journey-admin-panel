<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use Support\Contracts\MapperInterface;

abstract class EnvServiceProvider extends ServiceProvider
{
    protected function getMapper(): MapperInterface
    {
        return $this->app->make(MapperInterface::class);
    }
}
