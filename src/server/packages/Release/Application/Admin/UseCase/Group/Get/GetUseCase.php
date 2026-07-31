<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Get;

use AdminUser\Domain\Models\Permission;
use Release\Application\Admin\Query\ReleaseGroupDetailQueryServiceInterface;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Support\Domain\Validation\FieldErrors;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private ReleaseGroupRepositoryInterface $repository,
        private ReleaseGroupDetailQueryServiceInterface $query,
    ) {
    }

    public function handle(GetInputData $inputData): GetOutputData
    {
        $this->authorizer->ensure(Permission::ReadRelease);

        $releaseGroupId = FieldErrors::single('releaseGroupId', static fn (): ReleaseGroupId => new ReleaseGroupId($inputData->releaseGroupId));

        if (is_null($found = $this->repository->find($releaseGroupId))) {
            throw new ResourceNotFoundException('ReleaseGroup', $releaseGroupId->value);
        }

        return new GetOutputData(
            $found,
            $this->query->findReferencedReleases($releaseGroupId),
        );
    }
}
