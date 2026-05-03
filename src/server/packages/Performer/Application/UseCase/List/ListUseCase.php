<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\List;

use AdminUser\Domain\Models\Permission;
use Performer\Domain\Models\PerformerRepositoryInterface;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\UseCaseError;

readonly class ListUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private PerformerRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<ListOutputData, UseCaseError>
     */
    public function handle(): Result
    {
        return $this->authorizer->require(Permission::ReadPerformer)
            ->andThen(fn () => $this->listPerformers());
    }

    /**
     * @return Result<ListOutputData, UseCaseError>
     */
    private function listPerformers(): Result
    {
        return new Ok(new ListOutputData($this->repository->all()));
    }
}
