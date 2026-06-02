<?php

declare(strict_types=1);

namespace Song\Application\Viewer\UseCase\Count;

use ResultType\Ok;
use ResultType\Result;
use Song\Application\Viewer\Query\SongQueryServiceInterface;
use Support\UseCase\Error\UseCaseError;

readonly class CountUseCase
{
    public function __construct(private SongQueryServiceInterface $query)
    {
    }

    /**
     * @return Result<CountOutputData, UseCaseError>
     */
    public function handle(): Result
    {
        return new Ok(new CountOutputData($this->query->countDisplayable()));
    }
}
