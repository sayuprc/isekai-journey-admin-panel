<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\Login;

use ResultType\Result;

interface LoginUseCaseInterface
{
    /**
     * @return Result<LoginOutputData, string>
     */
    public function handle(LoginInputData $inputData): Result;
}
