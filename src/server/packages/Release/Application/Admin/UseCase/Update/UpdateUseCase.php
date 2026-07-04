<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Update;

use AdminUser\Domain\Models\Permission;
use LogicException;
use Release\Domain\Models\ReleaseId;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Release\Domain\Services\ReleaseIntegrityService;
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
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

readonly class UpdateUseCase
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
     * @return Result<UpdateOutputData, UseCaseError>
     */
    public function handle(UpdateInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WriteRelease)
            ->andThen(fn () => $this->updateRelease($inputData));
    }

    /**
     * @return Result<UpdateOutputData, UseCaseError>
     */
    private function updateRelease(UpdateInputData $inputData): Result
    {
        return ReleaseId::create($inputData->releaseId)
            ->mapErr(fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(function (ReleaseId $releaseId) use ($inputData): Result {
                return $this->transaction->scope(function () use ($inputData, $releaseId): Result {
                    if (is_null($found = $this->repository->find($releaseId))) {
                        return new Err(new NotFoundError('Release', $releaseId->value));
                    }

                    // リリースの所属先グループは更新では変更しない。
                    $result = $this->service->prepareForUpdate(
                        $inputData->releaseId,
                        $found->releaseGroupId->value,
                        $inputData->name,
                        $inputData->releasedOn,
                        $inputData->description,
                        $inputData->jacketArtUrl,
                        $inputData->isDisplay,
                        $inputData->media,
                    );

                    if ($result->isErr()) {
                        return new Err($this->handleError($result->unwrapErr()));
                    }

                    $release = $this->repository->save($result->unwrap());

                    $this->recorder->record(
                        AuditAction::Update,
                        AuditTargetType::Release,
                        $release->releaseId,
                        $release->toArray(),
                    );

                    return new Ok(new UpdateOutputData($release));
                });
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
