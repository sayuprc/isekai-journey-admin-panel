<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\Get;

use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

interface GetUseCaseInterface
{
    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    public function handle(GetInputData $inputData): Result;
}
