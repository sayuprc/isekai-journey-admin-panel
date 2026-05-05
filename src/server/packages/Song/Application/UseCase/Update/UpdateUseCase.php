<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Update;

use AdminUser\Domain\Models\Permission;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\Assemble\SongAssembler;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Services\SongIntegrityService;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class UpdateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private SongRepositoryInterface $repository,
        private SongIntegrityService $service,
        private SongAssembler $assembler,
    ) {
    }

    /**
     * @return Result<UpdateOutputData, UseCaseError>
     */
    public function handle(UpdateInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WriteSong)
            ->andThen(fn () => $this->updateSong($inputData));
    }

    /**
     * @return Result<UpdateOutputData, UseCaseError>
     */
    private function updateSong(UpdateInputData $inputData): Result
    {
        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->prepareForUpdate(
                $inputData->songId,
                $inputData->title,
                $inputData->description,
                $inputData->lyricsLink,
                $inputData->typeValue,
                $inputData->isDisplay,
                $inputData->orderNo,
                $inputData->tags,
                $inputData->persons,
                $inputData->media,
            );

            if ($result->isErr()) {
                return new Err($this->handleError($result->unwrapErr()));
            }

            $song = $this->repository->save($result->unwrap());

            return new Ok(new UpdateOutputData($this->assembler->assemble($song)));
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
