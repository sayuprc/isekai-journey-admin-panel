<?php

declare(strict_types=1);

namespace Performer\Application\Interactors;

use Performer\Application\UseCase\Get\GetInputData;
use Performer\Application\UseCase\Get\GetOutputData;
use Performer\Application\UseCase\Get\GetUseCaseInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerRepositoryInterface;
use ResultType\Eager\Err;
use ResultType\Eager\Ok;
use ResultType\Result;

readonly class GetInteractor implements GetUseCaseInterface
{
    public function __construct(private PerformerRepositoryInterface $repository)
    {
    }

    /**
     * @return Result<GetOutputData, string>
     */
    public function handle(GetInputData $inputData): Result
    {
        if (is_null($found = $this->repository->find(new PerformerId($inputData->performerId)))) {
            return new Err("Performer not found: {$inputData->performerId}");
        }

        return new Ok(new GetOutputData($found));
    }
}
