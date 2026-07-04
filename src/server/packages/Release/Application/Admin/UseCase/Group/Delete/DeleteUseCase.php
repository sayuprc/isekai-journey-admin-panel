<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Delete;

use AdminUser\Domain\Models\Permission;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Release\Domain\Models\ReleaseRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\EntityRuleViolationError;
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
        private ReleaseGroupRepositoryInterface $repository,
        private ReleaseRepositoryInterface $releaseRepository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<null, UseCaseError>
     */
    public function handle(DeleteInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WriteRelease)
            ->andThen(fn () => $this->deleteReleaseGroup($inputData));
    }

    /**
     * @return Result<null, UseCaseError>
     */
    private function deleteReleaseGroup(DeleteInputData $inputData): Result
    {
        return ReleaseGroupId::create($inputData->releaseGroupId)
            ->mapErr(fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(fn (ReleaseGroupId $releaseGroupId): Result => $this->transaction->scope(function () use ($releaseGroupId): Result {
                $releaseGroup = $this->repository->find($releaseGroupId);

                if (is_null($releaseGroup)) {
                    return new Ok(null);
                }

                if ($this->releaseRepository->existsByReleaseGroupId($releaseGroupId)) {
                    return new Err(new BusinessLogicError('リリースが存在するため削除できません。'));
                }

                $this->repository->delete($releaseGroupId);

                $this->recorder->record(
                    AuditAction::Delete,
                    AuditTargetType::ReleaseGroup,
                    $releaseGroup->releaseGroupId,
                    $releaseGroup->toArray(),
                );

                return new Ok(null);
            }));
    }
}
