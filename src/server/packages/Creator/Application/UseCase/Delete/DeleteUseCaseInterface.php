<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Delete;

use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

interface DeleteUseCaseInterface
{
    /**
     * @return Result<null, UseCaseError>
     */
    public function handle(DeleteInputData $inputData): Result;
}
