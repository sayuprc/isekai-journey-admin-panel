<?php

declare(strict_types=1);

namespace Performer\Application\Interactors;

use Performer\Application\UseCase\Get\GetInputData;
use Performer\Application\UseCase\Get\GetOutputData;
use Performer\Application\UseCase\Get\GetUseCaseInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
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
        return PerformerId::create($inputData->performerId)
            // TODO エラーハンドリング強化
            ->mapErr(fn (): string => '')
            ->andThen(function (PerformerId $performerId): Result {
                if (is_null($found = $this->repository->find($performerId))) {
                    return new Err("Performer not found: {$performerId->value}");
                }

                return new Ok(new GetOutputData($found));
            });
    }
}
