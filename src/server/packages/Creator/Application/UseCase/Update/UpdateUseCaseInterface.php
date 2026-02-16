<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Update;

use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

interface UpdateUseCaseInterface
{
    /**
     * @return Result<UpdateOutputData, UseCaseError>
     */
    public function handle(UpdateInputData $inputData): Result;
}
