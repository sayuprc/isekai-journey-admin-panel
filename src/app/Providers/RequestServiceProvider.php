<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Shared\Mapper\MapperInterface;

class RequestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->auth();
    }

    private function auth(): void
    {
        $this->app->bind(\Auth\UseCases\Login\LoginRequest::class, function (): \Auth\UseCases\Login\LoginRequest {
            $request = $this->app->make(\App\Http\Requests\Auth\LoginRequest::class);
            assert($request instanceof \App\Http\Requests\Auth\LoginRequest);

            return $this->getMapper()->mapFromArray(\Auth\UseCases\Login\LoginRequest::class, $request->validated());
        });
    }

    protected function getMapper(): MapperInterface
    {
        return $this->app->make(MapperInterface::class);
    }
}
