<?php

declare(strict_types=1);

namespace Person\Application\UseCase\List;

use AdminUser\Domain\Models\Permission;
use Person\Domain\Models\PersonRepositoryInterface;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\UseCaseError;

readonly class ListUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private PersonRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<ListOutputData, UseCaseError>
     */
    public function handle(): Result
    {
        return $this->authorizer->require(Permission::ReadPerson)
            ->andThen(fn () => new Ok(new ListOutputData($this->repository->all())));
    }
}
