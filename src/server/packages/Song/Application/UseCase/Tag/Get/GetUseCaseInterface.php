<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Tag\Get;

use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

interface GetUseCaseInterface
{
    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    public function handle(GetInputData $inputData): Result;
}
