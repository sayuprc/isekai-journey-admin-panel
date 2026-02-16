<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\Login;

use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

interface LoginUseCaseInterface
{
    /**
     * @return Result<LoginOutputData, UseCaseError>
     */
    public function handle(LoginInputData $inputData): Result;
}
