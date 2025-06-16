<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\Login;

interface LoginUseCaseInterface
{
    public function handle(LoginInputData $inputData): LoginOutputData;
}
