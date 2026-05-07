<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\Create;

use AdminUser\Domain\Models\Permission;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Song\Domain\Services\SongTagIntegrityService;
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

readonly class CreateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private SongTagRepositoryInterface $repository,
        private SongTagIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<CreateOutputData, UseCaseError>
     */
    public function handle(CreateInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WriteSong)
            ->andThen(fn () => $this->createSongTag($inputData));
    }

    /**
     * @return Result<CreateOutputData, UseCaseError>
     */
    private function createSongTag(CreateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->prepareForCreate($inputData->name);

            if ($result->isErr()) {
                return new Err($this->handleError($result->unwrapErr()));
            }

            $tag = $result->unwrap();

            $this->repository->save($tag);

            $this->recorder->record(
                AuditAction::Create,
                AuditTargetType::SongTag,
                $tag->songTagId,
                $tag->toArray(),
            );

            return new Ok(new CreateOutputData($tag));
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
