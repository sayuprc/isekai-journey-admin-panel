<?php

declare(strict_types=1);

namespace User\Application\UseCase\Authenticate;

use ResultType\Result;

interface AuthenticateUseCaseInterface
{
    /**
     * @return Result<AuthenticateOutputData, string>
     */
    public function handle(AuthenticateInputData $inputData): Result;
}
