<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Auth\Application\Login\LoginInteractor;
use Auth\UseCases\Login\LoginRequest;
use Auth\UseCases\Login\LoginUseCaseInterface;

class AuthServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LoginUseCaseInterface::class, LoginInteractor::class);

        $this->app->bind(LoginRequest::class, function (): LoginRequest {
            $request = $this->app->make(\App\Http\Requests\Web\Auth\LoginRequest::class);
            assert($request instanceof \App\Http\Requests\Web\Auth\LoginRequest);

            return $this->getMapper()->map(LoginRequest::class, $request->validated());
        });
    }
}
