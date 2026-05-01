<?php

declare(strict_types=1);

namespace Song\Application\Interactors\Tag;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use Override;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\UseCase\Tag\Get\GetInputData;
use Song\Application\UseCase\Tag\Get\GetOutputData;
use Song\Application\UseCase\Tag\Get\GetUseCaseInterface;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
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
        private SongTagRepositoryInterface $repository,
    ) {
    }

    #[Override]
    public function handle(GetInputData $inputData): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::ReadSong)) {
            return new Err(new AuthorizationError());
        }

        return SongTagId::create($inputData->songTagId)
            ->mapErr(fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(function (SongTagId $songTagId): Result {
                if (is_null($found = $this->repository->find($songTagId))) {
                    return new Err(new NotFoundError('SongTag', $songTagId->value));
                }

                return new Ok(new GetOutputData($found));
            });
    }
}
