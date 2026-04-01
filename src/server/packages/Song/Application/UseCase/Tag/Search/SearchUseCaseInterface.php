<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Tag\Search;

use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

interface SearchUseCaseInterface
{
    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    public function handle(SearchInputData $inputData): Result;
}
