<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Shared\Mapper\MapperInterface;

abstract class RequestServiceProviderBase extends ServiceProvider
{
    protected function getMapper(): MapperInterface
    {
        return $this->app->make(MapperInterface::class);
    }
}
