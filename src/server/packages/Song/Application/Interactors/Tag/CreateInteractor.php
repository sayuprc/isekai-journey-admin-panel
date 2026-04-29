<?php

declare(strict_types=1);

namespace Song\Application\Interactors\Tag;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use LogicException;
use Override;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\UseCase\Tag\Create\CreateInputData;
use Song\Application\UseCase\Tag\Create\CreateOutputData;
use Song\Application\UseCase\Tag\Create\CreateUseCaseInterface;
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
use Support\UseCase\Error\UseCaseError;

readonly class CreateInteractor implements CreateUseCaseInterface
{
    public function __construct(
        private AuthContext $context,
        private TransactionInterface $transaction,
        private SongTagRepositoryInterface $repository,
        private SongTagIntegrityService $service,
    ) {
    }

    #[Override]
    public function handle(CreateInputData $inputData): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::WriteSong)) {
            return new Err(new AuthorizationError());
        }

        return $this->transaction->scope(function () use ($inputData): Result {
            $result = $this->service->prepareForCreate($inputData->name);

            if ($result->isErr()) {
                return new Err($this->handleError($result->unwrapErr()));
            }

            $tag = $result->unwrap();

            $this->repository->save($tag);

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
