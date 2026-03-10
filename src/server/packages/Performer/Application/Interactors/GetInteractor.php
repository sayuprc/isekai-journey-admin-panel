<?php

declare(strict_types=1);

namespace Performer\Application\Interactors;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use Override;
use Performer\Application\UseCase\Get\GetInputData;
use Performer\Application\UseCase\Get\GetOutputData;
use Performer\Application\UseCase\Get\GetUseCaseInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerRepositoryInterface;
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
        private PerformerRepositoryInterface $repository,
    ) {
    }

    #[Override]
    public function handle(GetInputData $inputData): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::ReadPerformer)) {
            return new Err(new AuthorizationError());
        }

        return PerformerId::create($inputData->performerId)
            ->mapErr(fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(function (PerformerId $performerId): Result {
                if (is_null($found = $this->repository->find($performerId))) {
                    return new Err(new NotFoundError('Performer', $performerId->value));
                }

                return new Ok(new GetOutputData($found));
            });
    }
}
