<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private SongRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<null, UseCaseError>
     */
    public function handle(DeleteInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WriteSong)
            ->andThen(fn () => $this->deleteSong($inputData));
    }

    /**
     * @return Result<null, UseCaseError>
     */
    private function deleteSong(DeleteInputData $inputData): Result
    {
        return SongId::create($inputData->songId)
            ->mapErr(fn (): UseCaseError => new InvalidInputError(['songId' => ['IDが不正です']]))
            ->andThen(function (SongId $songId): Result {
                $this->repository->delete($songId);

                return new Ok(null);
            });
    }
}
