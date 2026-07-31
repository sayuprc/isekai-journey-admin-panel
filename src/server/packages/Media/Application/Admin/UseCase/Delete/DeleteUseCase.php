<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaRepositoryInterface;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\DomainValidationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private MediaRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(DeleteInputData $inputData): void
    {
        $this->authorizer->ensure(Permission::WriteMedia);

        try {
            $mediaId = new MediaId($inputData->mediaId);
        } catch (InvalidDomainException) {
            throw new DomainValidationException(['mediaId' => ['IDが不正です']]);
        }

        $this->transaction->scope(function () use ($mediaId): void {
            $media = $this->repository->find($mediaId);

            if (is_null($media)) {
                return;
            }

            if ($this->repository->isUsed($mediaId)) {
                throw new BusinessRuleViolationException('このメディアは楽曲に使用されているため削除できません');
            }

            $this->repository->delete($mediaId);

            $this->recorder->record(
                AuditAction::Delete,
                AuditTargetType::Media,
                $media->mediaId,
                $media->toArray(),
            );
        });
    }
}
