<?php

declare(strict_types=1);

namespace AdminUser\Application\Interactors;

use AdminUser\Application\UseCase\List\ListInputData;
use AdminUser\Application\UseCase\List\ListOutputData;
use AdminUser\Application\UseCase\List\ListUseCaseInterface;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Override;

readonly class ListInteractor implements ListUseCaseInterface
{
    public function __construct(
        private AuthContext $context,
        private AdminUserRepositoryInterface $repository,
    ) {
    }

    #[Override]
    public function handle(ListInputData $inputData): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::ReadAdminUser)) {
            return new Err(new AuthorizationError());
        }

        return new Ok(new ListOutputData($this->repository->all()));
    }
}
