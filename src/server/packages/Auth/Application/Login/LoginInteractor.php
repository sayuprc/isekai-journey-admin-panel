<?php

declare(strict_types=1);

namespace Auth\Application\Login;

use Auth\UseCases\Login\LoginInputData;
use Auth\UseCases\Login\LoginOutputData;
use Auth\UseCases\Login\LoginUseCaseInterface;
use Illuminate\Auth\AuthManager;

class LoginInteractor implements LoginUseCaseInterface
{
    public function __construct(private readonly AuthManager $authManager)
    {
    }

    public function handle(LoginInputData $inputData): LoginOutputData
    {
        return new LoginOutputData($this->authManager->guard()->attempt($inputData->credentials));
    }
}
