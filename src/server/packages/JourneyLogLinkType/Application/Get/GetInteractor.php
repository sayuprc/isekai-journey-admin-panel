<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Get;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Get\GetInputData;
use JourneyLogLinkType\UseCases\Get\GetOutputData;
use JourneyLogLinkType\UseCases\Get\GetUseCaseInterface;
use Support\ResultType\Err;
use Support\ResultType\Ok;
use Support\ResultType\Result;

class GetInteractor implements GetUseCaseInterface
{
    public function __construct(private readonly JourneyLogLinkTypeRepositoryInterface $repository)
    {
    }

    /**
     * @return Result<GetOutputData, string>
     */
    public function handle(GetInputData $inputData): Result
    {
        if (is_null($found = $this->repository->find(new JourneyLogLinkTypeId($inputData->journeyLogLinkTypeId)))) {
            return new Err("JourneyLogLinkType not found: {$inputData->journeyLogLinkTypeId}");
        }

        return new Ok(new GetOutputData($found));
    }
}
