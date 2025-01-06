<?php

declare(strict_types=1);

namespace Auth\Application\Login;

use Auth\UseCases\Login\LoginRequest;
use Auth\UseCases\Login\LoginResponse;
use Auth\UseCases\Login\LoginUseCaseInterface;
use Illuminate\Auth\AuthManager;

class LoginInteractor implements LoginUseCaseInterface
{
    public function __construct(private readonly AuthManager $authManager)
    {
    }

    public function handle(LoginRequest $request): LoginResponse
    {
        return new LoginResponse($this->authManager->guard()->attempt($request->credentials));
    }
}
