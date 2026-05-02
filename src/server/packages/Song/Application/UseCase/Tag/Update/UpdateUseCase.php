<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Tag\Update;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use LogicException;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Song\Domain\Services\SongTagIntegrityService;
use Support\Contracts\TransactionInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

readonly class UpdateUseCase
{
    public function __construct(
        private AuthContext $context,
        private TransactionInterface $transaction,
        private SongTagRepositoryInterface $repository,
        private SongTagIntegrityService $service,
    ) {
    }

    /**
     * @return Result<UpdateOutputData, UseCaseError>
     */
    public function handle(UpdateInputData $inputData): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::WriteSong)) {
            return new Err(new AuthorizationError());
        }

        return SongTagId::create($inputData->songTagId)
            ->mapErr(fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(fn (SongTagId $songTagId): Result => $this->transaction->scope(function () use ($inputData, $songTagId): Result {
                if (is_null($this->repository->find($songTagId))) {
                    return new Err(new NotFoundError('SongTag', $songTagId->value));
                }

                $result = $this->service->prepareForUpdate($inputData->songTagId, $inputData->name, $inputData->orderNo);

                if ($result->isErr()) {
                    return new Err($this->handleError($result->unwrapErr()));
                }

                $tag = $result->unwrap();

                $this->repository->save($tag);

                return new Ok(new UpdateOutputData($tag));
            }));
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
