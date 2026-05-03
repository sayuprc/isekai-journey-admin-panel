<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Creator\Domain\Criteria\CreatorSearchCriteria;
use Creator\Domain\Models\CreatorRepositoryInterface;
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
        private CreatorRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    public function handle(SearchInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::ReadCreator)
            ->andThen(fn () => $this->searchCreators($inputData));
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    private function searchCreators(SearchInputData $inputData): Result
    {
        $criteria = new CreatorSearchCriteria(
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
