<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\ResetOrderNumbers;

use AdminUser\Domain\Models\Permission;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonRepositoryInterface;
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
        private PersonRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<ResetOrderNumbersOutputData, UseCaseError>
     */
    public function handle(): Result
    {
        return $this->authorizer->require(Permission::WritePerson)
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
                $person = $this->repository->find(PersonId::reconstruct($row['id']));

                if (is_null($person)) {
                    continue;
                }

                $this->recorder->record(
                    AuditAction::Update,
                    AuditTargetType::Person,
                    $person->personId,
                    $person->toArray(),
                );
            }

            return new Ok(new ResetOrderNumbersOutputData(count($updated)));
        });
    }
}
