<?php

declare(strict_types=1);

namespace Place\Application\Admin\UseCase\List;

use AdminUser\Domain\Models\Permission;
use Place\Domain\Models\PlaceRepositoryInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class ListUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private PlaceRepositoryInterface $repository,
    ) {
    }

    public function handle(): ListOutputData
    {
        $this->authorizer->authorize(Permission::ReadPlace);

        return new ListOutputData($this->repository->all());
    }
}
