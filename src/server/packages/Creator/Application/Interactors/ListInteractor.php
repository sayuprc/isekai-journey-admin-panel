<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use Creator\Application\UseCase\List\ListOutputData;
use Creator\Application\UseCase\List\ListUseCaseInterface;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Override;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;

readonly class ListInteractor implements ListUseCaseInterface
{
    public function __construct(
        private AuthContext $context,
        private CreatorRepositoryInterface $repository,
    ) {
    }

    #[Override]
    public function handle(): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::ReadCreator)) {
            return new Err(new AuthorizationError());
        }

        return new Ok(new ListOutputData($this->repository->all()));
    }
}
