<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\List;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use Performer\Domain\Models\PerformerRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\UseCaseError;

readonly class ListUseCase
{
    public function __construct(
        private AuthContext $context,
        private PerformerRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<ListOutputData, UseCaseError>
     */
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
