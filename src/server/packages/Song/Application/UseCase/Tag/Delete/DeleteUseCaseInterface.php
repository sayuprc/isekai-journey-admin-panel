<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Tag\Delete;

use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

interface DeleteUseCaseInterface
{
    /**
     * @return Result<null, UseCaseError>
     */
    public function handle(DeleteInputData $inputData): Result;
}
