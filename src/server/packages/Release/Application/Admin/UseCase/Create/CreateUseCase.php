<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Create;

use AdminUser\Domain\Models\Permission;
use LogicException;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Release\Domain\Services\ReleaseIntegrityService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class CreateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private ReleaseRepositoryInterface $repository,
        private ReleaseIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<CreateOutputData, UseCaseError>
     */
    public function handle(CreateInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WriteRelease)
            ->andThen(fn () => $this->createRelease($inputData));
    }

    /**
     * @return Result<CreateOutputData, UseCaseError>
     */
    private function createRelease(CreateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->prepareForCreate(
                $inputData->title,
                $inputData->typeValue,
                $inputData->distributionTypeValue,
                $inputData->releasedOn,
                $inputData->description,
                $inputData->isDisplay,
            );

            if ($result->isErr()) {
                return new Err($this->handleError($result->unwrapErr()));
            }

            $release = $this->repository->save($result->unwrap());

            $this->recorder->record(
                AuditAction::Create,
                AuditTargetType::Release,
                $release->releaseId,
                $release->toArray(),
            );

            return new Ok(new CreateOutputData($release));
        });
    }

    private function handleError(DomainError $error): UseCaseError
    {
        return match (true) {
            $error instanceof DomainValidationError => new InvalidInputError($error->errors),
            $error instanceof EntityRuleViolationError => new InvalidInputError([$error->field => [$error->message]]),
            default => throw new LogicException('予期しないドメインエラーが発生しました: ' . $error::class),
        };
    }
}
