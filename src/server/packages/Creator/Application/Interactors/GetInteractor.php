<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use Creator\Application\UseCase\Get\GetInputData;
use Creator\Application\UseCase\Get\GetOutputData;
use Creator\Application\UseCase\Get\GetUseCaseInterface;
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

readonly class GetInteractor implements GetUseCaseInterface
{
    public function __construct(
        private AuthContext $context,
        private CreatorRepositoryInterface $repository,
    ) {
    }

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
