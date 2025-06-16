<?php

declare(strict_types=1);

namespace JourneyLog\Application\Get;

use JourneyLog\Domain\Models\JourneyLogId;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\UseCases\Get\GetInputData;
use JourneyLog\UseCases\Get\GetOutputData;
use JourneyLog\UseCases\Get\GetUseCaseInterface;
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
