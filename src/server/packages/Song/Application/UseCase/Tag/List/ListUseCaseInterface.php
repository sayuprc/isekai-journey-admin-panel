<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Tag\List;

use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

interface ListUseCaseInterface
{
    /**
     * @return Result<ListOutputData, UseCaseError>
     */
    public function handle(): Result;
}
