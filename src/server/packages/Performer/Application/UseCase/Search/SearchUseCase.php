<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Performer\Domain\Criteria\PerformerSearchCriteria;
use Performer\Domain\Models\PerformerRepositoryInterface;
use ResultType\Ok;
use ResultType\Result;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\UseCaseError;

readonly class SearchUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private PerformerRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    public function handle(SearchInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::ReadPerformer)
            ->andThen(fn () => $this->searchPerformers($inputData));
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    private function searchPerformers(SearchInputData $inputData): Result
    {
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
