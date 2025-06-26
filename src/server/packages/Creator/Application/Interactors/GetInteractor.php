<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use Creator\Application\UseCase\Get\GetInputData;
use Creator\Application\UseCase\Get\GetOutputData;
use Creator\Application\UseCase\Get\GetUseCaseInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorRepositoryInterface;
use ResultType\Eager\Err;
use ResultType\Eager\Ok;
use ResultType\Result;

class GetInteractor implements GetUseCaseInterface
{
    public function __construct(private readonly CreatorRepositoryInterface $repository)
    {
    }

    /**
     * @return Result<GetOutputData, string>
     */
    public function handle(GetInputData $inputData): Result
    {
        if (is_null($found = $this->repository->find(new CreatorId($inputData->creatorId)))) {
            return new Err("Creator not found: {$inputData->creatorId}");
        }

        return new Ok(new GetOutputData($found));
    }
}
