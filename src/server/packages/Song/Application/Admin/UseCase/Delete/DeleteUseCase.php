<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\DomainValidationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private SongRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(DeleteInputData $inputData): void
    {
        $this->authorizer->ensure(Permission::WriteSong);

        try {
            $songId = new SongId($inputData->songId);
        } catch (InvalidDomainException) {
            throw new DomainValidationException(['songId' => ['IDが不正です']]);
        }

        $this->transaction->scope(function () use ($songId): void {
            $song = $this->repository->find($songId);

            if (is_null($song)) {
                throw new ResourceNotFoundException('Song', $songId->value);
            }

            $this->repository->delete($songId);

            $this->recorder->record(
                AuditAction::Delete,
                AuditTargetType::Song,
                $song->songId,
                $song->toArray(),
            );
        });
    }
}
