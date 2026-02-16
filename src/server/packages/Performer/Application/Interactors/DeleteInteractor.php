<?php

declare(strict_types=1);

namespace Performer\Application\Interactors;

use Performer\Application\UseCase\Delete\DeleteInputData;
use Performer\Application\UseCase\Delete\DeleteUseCaseInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerRepositoryInterface;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(private PerformerRepositoryInterface $repository)
    {
    }

    public function handle(DeleteInputData $inputData): Result
    {
        return PerformerId::create($inputData->performerId)
            ->mapErr(fn (): UseCaseError => new InvalidInputError(['performerId' => ['IDが不正です']]))
            ->andThen(function (PerformerId $performerId): Result {
                $this->repository->delete($performerId);

                return new Ok(null);
            });
    }
}
