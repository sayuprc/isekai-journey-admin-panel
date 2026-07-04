<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\Get;

use AdminUser\Domain\Models\Permission;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private SongTagRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    public function handle(GetInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::ReadSong)
            ->andThen(fn () => $this->getSongTag($inputData));
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    private function getSongTag(GetInputData $inputData): Result
    {
        return SongTagId::create($inputData->songTagId)
            ->mapErr(static fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(function (SongTagId $songTagId): Result {
                if (is_null($found = $this->repository->find($songTagId))) {
                    return new Err(new NotFoundError('SongTag', $songTagId->value));
                }

                return new Ok(new GetOutputData($found));
            });
    }
}
