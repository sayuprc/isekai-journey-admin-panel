<?php

declare(strict_types=1);

namespace Media\Application\UseCase\Create;

use AdminUser\Domain\Models\Permission;
use LogicException;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Domain\Services\MediaIntegrityService;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class CreateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private MediaRepositoryInterface $repository,
        private MediaIntegrityService $service,
    ) {
    }

    /**
     * @return Result<CreateOutputData, UseCaseError>
     */
    public function handle(CreateInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WriteSong)
            ->andThen(fn () => $this->createMedia($inputData));
    }

    /**
     * @return Result<CreateOutputData, UseCaseError>
     */
    private function createMedia(CreateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->prepareForCreate(
                $inputData->title,
                $inputData->url,
                $inputData->typeValue,
                $inputData->isDisplay,
            );

            if ($result->isErr()) {
                return new Err($this->handleError($result->unwrapErr()));
            }

            $media = $this->repository->save($result->unwrap());

            return new Ok(new CreateOutputData($media));
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
