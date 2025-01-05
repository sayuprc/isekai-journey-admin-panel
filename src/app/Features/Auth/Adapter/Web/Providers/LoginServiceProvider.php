<?php

declare(strict_types=1);

namespace App\Features\Auth\Adapter\Web\Providers;

use App\Features\Auth\Adapter\Web\Requests\LoginRequest as WebRequest;
use App\Features\Auth\UseCases\Login\LoginRequest;
use App\Providers\RequestServiceProviderBase;

class LoginServiceProvider extends RequestServiceProviderBase
{
    public function register(): void
    {
        $this->app->bind(LoginRequest::class, function (): LoginRequest {
            $request = $this->app->make(WebRequest::class);
            assert($request instanceof WebRequest);

            return $this->getMapper()->mapFromArray(LoginRequest::class, $request->validated());
        });
    }
}
