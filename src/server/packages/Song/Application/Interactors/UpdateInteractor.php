<?php

declare(strict_types=1);

namespace Song\Application\Interactors;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\Assemble\SongAssembler;
use Song\Application\UseCase\Update\UpdateInputData;
use Song\Application\UseCase\Update\UpdateOutputData;
use Song\Application\UseCase\Update\UpdateUseCaseInterface;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Services\SongIntegrityService;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainRuleViolationError;
use Support\Domain\Error\DomainValidationError;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class UpdateInteractor implements UpdateUseCaseInterface
{
    public function __construct(
        private AuthContext $context,
        private TransactionInterface $transaction,
        private SongRepositoryInterface $repository,
        private SongIntegrityService $service,
        private SongAssembler $assembler,
    ) {
    }

    public function handle(UpdateInputData $inputData): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::WriteSong)) {
            return new Err(new AuthorizationError());
        }

        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->prepareForUpdate(
                $inputData->songId,
                $inputData->title,
                $inputData->description,
                $inputData->songTypeValue,
                $inputData->orderNo,
                $inputData->arrangers,
                $inputData->composers,
                $inputData->lyricists,
            );

            if ($result->isErr()) {
                return new Err($this->handleError($result->unwrapErr()));
            }

            $song = $result->unwrap();

            $this->repository->save($song);

            return new Ok(new UpdateOutputData($this->assembler->assemble($song)));
        });
    }

    private function handleError(DomainError $error): UseCaseError
    {
        return match (true) {
            $error instanceof DomainValidationError => new InvalidInputError($error->errors),
            $error instanceof DomainRuleViolationError => new InvalidInputError([$error->field => [$error->message]]),
            default => throw new LogicException('予期しないドメインエラーが発生しました: ' . $error::class),
        };
    }
}
