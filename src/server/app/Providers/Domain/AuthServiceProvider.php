<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use App\Http\Requests\Web\Auth\LoginRequest;
use Auth\Application\Interactors\LoginInteractor;
use Auth\Application\UseCase\Login\LoginInputData;
use Auth\Application\UseCase\Login\LoginUseCaseInterface;

class AuthServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LoginUseCaseInterface::class, LoginInteractor::class);

        $this->app->bind(LoginInputData::class, function (): LoginInputData {
            $request = $this->app->make(LoginRequest::class);

            return $this->getMapper()->map(LoginInputData::class, $request->validated());
        });
    }
}
