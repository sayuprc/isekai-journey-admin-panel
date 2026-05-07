<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Update;

use AdminUser\Domain\Models\Permission;
use LogicException;
use Person\Domain\Models\PersonRepositoryInterface;
use Person\Domain\Services\PersonIntegrityService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class UpdateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private PersonRepositoryInterface $repository,
        private PersonIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<UpdateOutputData, UseCaseError>
     */
    public function handle(UpdateInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WritePerson)
            ->andThen(fn () => $this->updatePerson($inputData));
    }

    /**
     * @return Result<UpdateOutputData, UseCaseError>
     */
    private function updatePerson(UpdateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->prepareForUpdate($inputData->personId, $inputData->name, $inputData->orderNo);

            if ($result->isErr()) {
                return new Err($this->handleError($result->unwrapErr()));
            }

            $person = $result->unwrap();

            $this->repository->save($person);

            $this->recorder->record(
                AuditAction::Update,
                AuditTargetType::Person,
                $person->personId,
                $person->toArray(),
            );

            return new Ok(new UpdateOutputData($person));
        });
    }

    private function handleError(DomainError $error): UseCaseError
    {
        return match (true) {
            $error instanceof DomainValidationError => new InvalidInputError($error->errors),
            $error instanceof EntityRuleViolationError => new InvalidInputError([$error->field => [$error->message]]),
            $error instanceof BusinessRuleViolationError => new BusinessLogicError($error->message),
            default => throw new LogicException('予期しないドメインエラーが発生しました: ' . $error::class),
        };
    }
}
