<?php

declare(strict_types=1);

namespace Song\Application\Interactors;

use ResultType\Ok;
use ResultType\Result;
use Song\Application\UseCase\Delete\DeleteInputData;
use Song\Application\UseCase\Delete\DeleteUseCaseInterface;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(private SongRepositoryInterface $repository)
    {
    }

    public function handle(DeleteInputData $inputData): Result
    {
        return SongId::create($inputData->songId)
            ->mapErr(fn (): UseCaseError => new InvalidInputError(['songId' => ['IDが不正です']]))
            ->andThen(function (SongId $songId): Result {
                $this->repository->delete($songId);

                return new Ok(null);
            });
    }
}
