<?php

declare(strict_types=1);

namespace JourneyLog\Application\Interactors;

use JourneyLog\Application\UseCase\Get\GetInputData;
use JourneyLog\Application\UseCase\Get\GetOutputData;
use JourneyLog\Application\UseCase\Get\GetUseCaseInterface;
use JourneyLog\Domain\Models\JourneyLogId;
use JourneyLog\Domain\Models\JourneyLogRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;

class GetInteractor implements GetUseCaseInterface
{
    public function __construct(private readonly JourneyLogRepositoryInterface $repository)
    {
    }

    /**
     * @return Result<GetOutputData, string>
     */
    public function handle(GetInputData $inputData): Result
    {
        if (is_null($found = $this->repository->find(new JourneyLogId($inputData->journeyLogId)))) {
            return new Err("JourneyLog not found: {$inputData->journeyLogId}");
        }

        return new Ok(new GetOutputData($found));
    }
}
