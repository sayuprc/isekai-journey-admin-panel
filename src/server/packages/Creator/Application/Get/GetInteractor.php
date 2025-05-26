<?php

declare(strict_types=1);

namespace Creator\Application\Get;

use Creator\Domain\Models\CreatorId;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\UseCases\Get\GetInputData;
use Creator\UseCases\Get\GetOutputData;
use Creator\UseCases\Get\GetUseCaseInterface;
use Support\ResultType\Err;
use Support\ResultType\Ok;
use Support\ResultType\Result;

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
