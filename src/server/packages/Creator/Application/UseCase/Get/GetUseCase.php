<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Get;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

readonly class GetUseCase
{
    public function __construct(
        private AuthContext $context,
        private CreatorRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    public function handle(GetInputData $inputData): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::ReadCreator)) {
            return new Err(new AuthorizationError());
        }

        return CreatorId::create($inputData->creatorId)
            ->mapErr(fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(function (CreatorId $creatorId): Result {
                if (is_null($found = $this->repository->find($creatorId))) {
                    return new Err(new NotFoundError('Creator', $creatorId->value));
                }

                return new Ok(new GetOutputData($found));
            });
    }
}
