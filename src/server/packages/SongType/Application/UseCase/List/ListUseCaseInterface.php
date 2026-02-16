<?php

declare(strict_types=1);

namespace SongType\Application\UseCase\List;

use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

interface ListUseCaseInterface
{
    /**
     * @return Result<ListOutputData, UseCaseError>
     */
    public function handle(): Result;
}
