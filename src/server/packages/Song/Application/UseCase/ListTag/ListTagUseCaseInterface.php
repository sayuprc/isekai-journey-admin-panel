<?php

declare(strict_types=1);

namespace Song\Application\UseCase\ListTag;

use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

interface ListTagUseCaseInterface
{
    /**
     * @return Result<ListTagOutputData, UseCaseError>
     */
    public function handle(): Result;
}
