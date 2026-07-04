<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Update;

use AdminUser\Domain\Models\Permission;
use LogicException;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Release\Domain\Services\ReleaseGroupIntegrityService;
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
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

readonly class UpdateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private ReleaseGroupRepositoryInterface $repository,
        private ReleaseGroupIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<UpdateOutputData, UseCaseError>
     */
    public function handle(UpdateInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WriteRelease)
            ->andThen(fn () => $this->updateReleaseGroup($inputData));
    }

    /**
     * @return Result<UpdateOutputData, UseCaseError>
     */
    private function updateReleaseGroup(UpdateInputData $inputData): Result
    {
        return ReleaseGroupId::create($inputData->releaseGroupId)
            ->mapErr(fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(function (ReleaseGroupId $releaseGroupId) use ($inputData): Result {
                return $this->transaction->scope(function () use ($inputData, $releaseGroupId): Result {
                    if (is_null($this->repository->find($releaseGroupId))) {
                        return new Err(new NotFoundError('ReleaseGroup', $releaseGroupId->value));
                    }

                    $result = $this->service->prepareForUpdate(
                        $inputData->releaseGroupId,
                        $inputData->title,
                        $inputData->typeValue,
                        $inputData->description,
                        $inputData->isDisplay,
                    );

                    if ($result->isErr()) {
                        return new Err($this->handleError($result->unwrapErr()));
                    }

                    $releaseGroup = $this->repository->save($result->unwrap());

                    $this->recorder->record(
                        AuditAction::Update,
                        AuditTargetType::ReleaseGroup,
                        $releaseGroup->releaseGroupId,
                        $releaseGroup->toArray(),
                    );

                    return new Ok(new UpdateOutputData($releaseGroup));
                });
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
