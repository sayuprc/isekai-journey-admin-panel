<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Get;

use AdminUser\Domain\Models\Permission;
use Release\Application\Admin\Query\ReleaseGroupDetailQueryServiceInterface;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Domain\Error\EntityRuleViolationError;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private ReleaseGroupRepositoryInterface $repository,
        private ReleaseGroupDetailQueryServiceInterface $query,
    ) {
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    public function handle(GetInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::ReadRelease)
            ->andThen(fn () => $this->getReleaseGroup($inputData));
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    private function getReleaseGroup(GetInputData $inputData): Result
    {
        return ReleaseGroupId::create($inputData->releaseGroupId)
            ->mapErr(fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(function (ReleaseGroupId $releaseGroupId): Result {
                if (is_null($found = $this->repository->find($releaseGroupId))) {
                    return new Err(new NotFoundError('ReleaseGroup', $releaseGroupId->value));
                }

                return new Ok(new GetOutputData(
                    $found,
                    $this->query->findReferencedReleases($releaseGroupId),
                ));
            });
    }
}
