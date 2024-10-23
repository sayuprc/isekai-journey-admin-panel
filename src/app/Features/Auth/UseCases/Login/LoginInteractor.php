<?php

declare(strict_types=1);

namespace App\Features\Auth\UseCases\Login;

use Illuminate\Auth\AuthManager;

class LoginInteractor
{
    public function __construct(private readonly AuthManager $authManager)
    {
    }

    public function handle(LoginRequest $request): LoginResponse
    {
        return new LoginResponse($this->authManager->guard()->attempt($request->credentials));
    }
}
