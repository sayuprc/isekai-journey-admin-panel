<?php

declare(strict_types=1);

namespace App\Providers\Domain;

use Auth\Application\Interactors\LoginInteractor;
use Auth\Application\UseCase\Login\LoginInputData;
use Auth\Application\UseCase\Login\LoginUseCaseInterface;
use Illuminate\Http\Request;

class AuthServiceProvider extends EnvServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LoginUseCaseInterface::class, LoginInteractor::class);

        $this->app->bind(LoginInputData::class, function (): LoginInputData {
            $request = $this->app->make(Request::class);

            return $this->getMapper()->map(LoginInputData::class, $request->all());
        });
    }
}
