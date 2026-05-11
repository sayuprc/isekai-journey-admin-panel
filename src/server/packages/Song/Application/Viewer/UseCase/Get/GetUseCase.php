<?php

declare(strict_types=1);

namespace Song\Application\Viewer\UseCase\Get;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\Viewer\Query\SongQueryServiceInterface;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

readonly class GetUseCase
{
    public function __construct(private SongQueryServiceInterface $query)
    {
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    public function handle(GetInputData $inputData): Result
    {
        $song = $this->query->get($inputData->songId);

        if (is_null($song)) {
            return new Err(new NotFoundError('楽曲', $inputData->songId));
        }

        return new Ok(new GetOutputData($song));
    }
}
