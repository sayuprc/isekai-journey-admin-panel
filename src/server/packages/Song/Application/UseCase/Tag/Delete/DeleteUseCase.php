<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Tag\Delete;

use AdminUser\Domain\Models\Permission;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\Contracts\AuditLog\AuditAction;
use Support\Contracts\AuditLog\AuditLogRecorderInterface;
use Support\Contracts\AuditLog\AuditTargetType;
use Support\Contracts\TransactionInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private SongTagRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<null, UseCaseError>
     */
    public function handle(DeleteInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WriteSong)
            ->andThen(fn () => $this->deleteSongTag($inputData));
    }

    /**
     * @return Result<null, UseCaseError>
     */
    private function deleteSongTag(DeleteInputData $inputData): Result
    {
        return SongTagId::create($inputData->songTagId)
            ->mapErr(fn (): UseCaseError => new InvalidInputError(['songTagId' => ['IDが不正です']]))
            ->andThen(fn (SongTagId $songTagId): Result => $this->transaction->scope(function () use ($songTagId): Result {
                $tag = $this->repository->find($songTagId);

                if (is_null($tag)) {
                    return new Ok(null);
                }

                if ($this->repository->isUsed($songTagId)) {
                    return new Err(new BusinessLogicError('この楽曲タグは楽曲に使用されているため削除できません'));
                }

                $this->repository->delete($songTagId);

                $this->recorder->record(
                    AuditAction::Delete,
                    AuditTargetType::SongTag,
                    $tag->songTagId->value,
                    [
                        'songTagId' => $tag->songTagId->value,
                        'name' => $tag->name->value,
                        'orderNo' => $tag->orderNo->value,
                    ],
                );

                return new Ok(null);
            }));
    }
}
