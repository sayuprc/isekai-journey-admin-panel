<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\Authenticate;

use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

interface AuthenticateUseCaseInterface
{
    /**
     * @return Result<AuthenticateOutputData, UseCaseError>
     */
    public function handle(AuthenticateInputData $inputData): Result;
}
