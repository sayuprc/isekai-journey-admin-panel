<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\ResetOrderNumbers;

use AdminUser\Domain\Models\Permission;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\UseCaseError;

readonly class ResetOrderNumbersUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private ReleaseGroupRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<ResetOrderNumbersOutputData, UseCaseError>
     */
    public function handle(): Result
    {
        return $this->authorizer->require(Permission::WriteRelease)
            ->andThen(fn () => $this->reset());
    }

    /**
     * @return Result<ResetOrderNumbersOutputData, UseCaseError>
     */
    private function reset(): Result
    {
        return $this->transaction->scope(function (): Result {
            $updated = $this->repository->resetOrderNumbers();

            foreach ($updated as $row) {
                $group = $this->repository->find(ReleaseGroupId::reconstruct($row['id']));

                if (is_null($group)) {
                    continue;
                }

                $this->recorder->record(
                    AuditAction::Update,
                    AuditTargetType::ReleaseGroup,
                    $group->releaseGroupId,
                    $group->toArray(),
                );
            }

            return new Ok(new ResetOrderNumbersOutputData(count($updated)));
        });
    }
}
