<?php

declare(strict_types=1);

namespace App\Providers;

use App\Shared\Mapper\MapperInterface;
use Illuminate\Support\ServiceProvider;

abstract class RequestServiceProviderBase extends ServiceProvider
{
    protected function getMapper(): MapperInterface
    {
        return $this->app->make(MapperInterface::class);
    }
}
