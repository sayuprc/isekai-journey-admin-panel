<?php

declare(strict_types=1);

namespace SongAttribute\Application\UseCase\List;

use ResultType\Result;
use UseCaseError\UseCaseError;

interface ListUseCaseInterface
{
    /**
     * @return Result<ListOutputData, UseCaseError>
     */
    public function handle(): Result;
}
