<?php

declare(strict_types=1);

namespace Auth\Adapter\Web\Providers;

use App\Providers\RequestServiceProviderBase;
use Auth\Adapter\Web\Requests\LoginRequest as WebRequest;
use Auth\UseCases\Login\LoginRequest;

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
