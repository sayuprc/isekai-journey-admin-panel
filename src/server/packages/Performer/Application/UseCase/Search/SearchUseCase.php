<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use Performer\Domain\Criteria\PerformerSearchCriteria;
use Performer\Domain\Models\PerformerRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\UseCaseError;

readonly class SearchUseCase
{
    public function __construct(
        private AuthContext $context,
        private PerformerRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    public function handle(SearchInputData $inputData): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::ReadPerformer)) {
            return new Err(new AuthorizationError());
        }

        $criteria = new PerformerSearchCriteria(
            $inputData->name === Arg::Optional
                ? new None()
                : new Some($inputData->name),
            $inputData->sort,
            $inputData->order,
            $inputData->page,
            $inputData->perPage,
        );

        return new Ok(
            new SearchOutputData(
                $this->repository->search($criteria),
                $this->repository->maxPage($criteria),
            ),
        );
    }
}
