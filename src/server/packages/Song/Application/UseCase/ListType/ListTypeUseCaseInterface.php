<?php

declare(strict_types=1);

namespace Song\Application\UseCase\ListType;

use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

interface ListTypeUseCaseInterface
{
    /**
     * @return Result<ListTypeOutputData, UseCaseError>
     */
    public function handle(): Result;
}
