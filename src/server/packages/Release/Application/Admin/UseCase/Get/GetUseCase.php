<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Get;

use AdminUser\Domain\Models\Permission;
use Release\Application\Admin\Query\ReleaseDetailQueryServiceInterface;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Release\Domain\Models\ReleaseId;
use Release\Domain\Models\ReleaseRepositoryInterface;
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
        private ReleaseRepositoryInterface $repository,
        private ReleaseGroupRepositoryInterface $groupRepository,
        private ReleaseDetailQueryServiceInterface $query,
    ) {
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    public function handle(GetInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::ReadRelease)
            ->andThen(fn () => $this->getRelease($inputData));
    }

    /**
     * @return Result<GetOutputData, UseCaseError>
     */
    private function getRelease(GetInputData $inputData): Result
    {
        return ReleaseId::create($inputData->releaseId)
            ->mapErr(static fn (EntityRuleViolationError $e): UseCaseError => new InvalidInputError([$e->field => [$e->message]]))
            ->andThen(function (ReleaseId $releaseId): Result {
                if (is_null($found = $this->repository->find($releaseId))) {
                    return new Err(new NotFoundError('Release', $releaseId->value));
                }

                if (is_null($group = $this->groupRepository->find($found->releaseGroupId))) {
                    return new Err(new NotFoundError('ReleaseGroup', $found->releaseGroupId->value));
                }

                return new Ok(new GetOutputData(
                    $found,
                    $group,
                    $this->query->findReferencedSongs($releaseId),
                ));
            });
    }
}
