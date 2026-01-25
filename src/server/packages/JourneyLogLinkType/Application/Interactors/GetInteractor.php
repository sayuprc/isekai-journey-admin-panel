<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Interactors;

use JourneyLogLinkType\Application\UseCase\Get\GetInputData;
use JourneyLogLinkType\Application\UseCase\Get\GetOutputData;
use JourneyLogLinkType\Application\UseCase\Get\GetUseCaseInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;

readonly class GetInteractor implements GetUseCaseInterface
{
    public function __construct(private JourneyLogLinkTypeRepositoryInterface $repository)
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
