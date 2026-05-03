<?php

declare(strict_types=1);

namespace AdminUser\Application\UseCase\List;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Permission;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\UseCaseError;

readonly class ListUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private AdminUserRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<ListOutputData, UseCaseError>
     */
    public function handle(): Result
    {
        return $this->authorizer->require(Permission::ReadAdminUser)
            ->andThen(fn () => $this->listAdminUsers());
    }

    /**
     * @return Result<ListOutputData, UseCaseError>
     */
    private function listAdminUsers(): Result
    {
        return new Ok(new ListOutputData($this->repository->all()));
    }
}
