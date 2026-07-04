<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonRepositoryInterface;
use Person\Domain\Services\PersonUsageCheckerInterface;
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
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private PersonRepositoryInterface $repository,
        private PersonUsageCheckerInterface $usageChecker,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<null, UseCaseError>
     */
    public function handle(DeleteInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WritePerson)
            ->andThen(fn () => $this->deletePerson($inputData));
    }

    /**
     * @return Result<null, UseCaseError>
     */
    private function deletePerson(DeleteInputData $inputData): Result
    {
        return PersonId::create($inputData->personId)
            ->mapErr(static fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(fn (PersonId $personId): Result => $this->transaction->scope(function () use ($personId): Result {
                $person = $this->repository->find($personId);

                if (is_null($person)) {
                    return new Err(new NotFoundError('Person', $personId->value));
                }

                if ($this->usageChecker->isUsed($personId)) {
                    return new Err(new BusinessLogicError('この人物は楽曲に使用されているため削除できません'));
                }

                $this->repository->delete($personId);

                $this->recorder->record(
                    AuditAction::Delete,
                    AuditTargetType::Person,
                    $person->personId,
                    $person->toArray(),
                );

                return new Ok(null);
            }));
    }
}
