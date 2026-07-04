<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use Release\Domain\Models\ReleaseId;
use Release\Domain\Models\ReleaseRepositoryInterface;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private ReleaseRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<null, UseCaseError>
     */
    public function handle(DeleteInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WriteRelease)
            ->andThen(fn () => $this->deleteRelease($inputData));
    }

    /**
     * @return Result<null, UseCaseError>
     */
    private function deleteRelease(DeleteInputData $inputData): Result
    {
        return ReleaseId::create($inputData->releaseId)
            ->mapErr(static fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(fn (ReleaseId $releaseId): Result => $this->transaction->scope(function () use ($releaseId): Result {
                $release = $this->repository->find($releaseId);

                if (is_null($release)) {
                    return new Ok(null);
                }

                $this->repository->delete($releaseId);

                $this->recorder->record(
                    AuditAction::Delete,
                    AuditTargetType::Release,
                    $release->releaseId,
                    $release->toArray(),
                );

                return new Ok(null);
            }));
    }
}
