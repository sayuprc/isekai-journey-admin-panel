<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private MediaRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<null, UseCaseError>
     */
    public function handle(DeleteInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WriteMedia)
            ->andThen(fn () => $this->deleteMedia($inputData));
    }

    /**
     * @return Result<null, UseCaseError>
     */
    private function deleteMedia(DeleteInputData $inputData): Result
    {
        return MediaId::create($inputData->mediaId)
            ->mapErr(fn (): UseCaseError => new InvalidInputError(['mediaId' => ['IDが不正です']]))
            ->andThen(fn (MediaId $mediaId): Result => $this->transaction->scope(function () use ($mediaId): Result {
                $media = $this->repository->find($mediaId);

                if (is_null($media)) {
                    return new Ok(null);
                }

                if ($this->repository->isUsed($mediaId)) {
                    return new Err(new BusinessLogicError('このメディアは楽曲に使用されているため削除できません'));
                }

                $this->repository->delete($mediaId);

                $this->recorder->record(
                    AuditAction::Delete,
                    AuditTargetType::Media,
                    $media->mediaId,
                    $media->toArray(),
                );

                return new Ok(null);
            }));
    }
}
