<?php

declare(strict_types=1);

namespace Song\Application\UseCase\ListAttribute;

use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

interface ListAttributeUseCaseInterface
{
    /**
     * @return Result<ListAttributeOutputData, UseCaseError>
     */
    public function handle(): Result;
}
