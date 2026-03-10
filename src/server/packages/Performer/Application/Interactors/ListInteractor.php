<?php

declare(strict_types=1);

namespace Performer\Application\Interactors;

use Override;
use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use Performer\Application\UseCase\List\ListOutputData;
use Performer\Application\UseCase\List\ListUseCaseInterface;
use Performer\Domain\Models\PerformerRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;

readonly class ListInteractor implements ListUseCaseInterface
{
    public function __construct(
        private AuthContext $context,
        private PerformerRepositoryInterface $repository,
    ) {
    }

    #[Override]
    public function handle(): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::ReadPerformer)) {
            return new Err(new AuthorizationError());
        }

        return new Ok(new ListOutputData($this->repository->all()));
    }
}
