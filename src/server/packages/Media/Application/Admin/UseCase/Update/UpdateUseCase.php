<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Update;

use AdminUser\Domain\Models\Permission;
use LogicException;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Domain\Services\MediaIntegrityService;
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
        private MediaRepositoryInterface $repository,
        private MediaIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<UpdateOutputData, UseCaseError>
     */
    public function handle(UpdateInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WriteMedia)
            ->andThen(fn () => $this->updateMedia($inputData));
    }

    /**
     * @return Result<UpdateOutputData, UseCaseError>
     */
    private function updateMedia(UpdateInputData $inputData): Result
    {
        return MediaId::create($inputData->mediaId)
            ->mapErr(static fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(fn (MediaId $mediaId): Result => $this->transaction->scope(function () use ($inputData, $mediaId): Result {
                if (is_null($this->repository->find($mediaId))) {
                    return new Err(new NotFoundError('Media', $mediaId->value));
                }

                $result = $this->service->prepareForUpdate(
                    $inputData->mediaId,
                    $inputData->title,
                    $inputData->url,
                    $inputData->publishedAt,
                    $inputData->typeValue,
                    $inputData->isDisplay,
                );

                if ($result->isErr()) {
                    return new Err($this->handleError($result->unwrapErr()));
                }

                $media = $this->repository->save($result->unwrap());

                $this->recorder->record(
                    AuditAction::Update,
                    AuditTargetType::Media,
                    $media->mediaId,
                    $media->toArray(),
                );

                return new Ok(new UpdateOutputData($media));
            }));
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
