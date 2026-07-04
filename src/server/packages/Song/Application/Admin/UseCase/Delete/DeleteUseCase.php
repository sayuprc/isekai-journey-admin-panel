<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private SongRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
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
            ->mapErr(static fn (): UseCaseError => new InvalidInputError(['songId' => ['IDが不正です']]))
            ->andThen(fn (SongId $songId): Result => $this->transaction->scope(function () use ($songId): Result {
                $song = $this->repository->find($songId);

                if (is_null($song)) {
                    return new Err(new NotFoundError('Song', $songId->value));
                }

                $this->repository->delete($songId);

                $this->recorder->record(
                    AuditAction::Delete,
                    AuditTargetType::Song,
                    $song->songId,
                    $song->toArray(),
                );

                return new Ok(null);
            }));
    }
}
