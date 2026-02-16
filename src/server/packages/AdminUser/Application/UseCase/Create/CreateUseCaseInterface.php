<?php

declare(strict_types=1);

namespace AdminUser\Application\UseCase\Create;

use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

interface CreateUseCaseInterface
{
    /**
     * @return Result<CreateOutputData, UseCaseError>
     */
    public function handle(CreateInputData $inputData): Result;
}
