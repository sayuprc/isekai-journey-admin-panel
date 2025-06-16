<?php

declare(strict_types=1);

namespace Auth\Application\Interactors;

use Auth\Application\UseCase\Login\LoginInputData;
use Auth\Application\UseCase\Login\LoginOutputData;
use Auth\Application\UseCase\Login\LoginUseCaseInterface;
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
