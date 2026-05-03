<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\List;

use AdminUser\Domain\Models\Permission;
use Creator\Domain\Models\CreatorRepositoryInterface;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\UseCaseError;

readonly class ListUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private CreatorRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<ListOutputData, UseCaseError>
     */
    public function handle(): Result
    {
        return $this->authorizer->require(Permission::ReadCreator)
            ->andThen(fn () => $this->listCreators());
    }

    /**
     * @return Result<ListOutputData, UseCaseError>
     */
    private function listCreators(): Result
    {
        return new Ok(new ListOutputData($this->repository->all()));
    }
}
