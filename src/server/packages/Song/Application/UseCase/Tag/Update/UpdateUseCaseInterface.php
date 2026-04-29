<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Tag\Update;

use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

interface UpdateUseCaseInterface
{
    /**
     * @return Result<UpdateOutputData, UseCaseError>
     */
    public function handle(UpdateInputData $inputData): Result;
}
