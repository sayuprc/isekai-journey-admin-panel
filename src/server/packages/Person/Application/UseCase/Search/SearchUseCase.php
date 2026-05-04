<?php

declare(strict_types=1);

namespace Person\Application\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Person\Domain\Criteria\PersonSearchCriteria;
use Person\Domain\Models\PersonRepositoryInterface;
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
        private PersonRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    public function handle(SearchInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::ReadPerson)
            ->andThen(fn () => $this->searchPersons($inputData));
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    private function searchPersons(SearchInputData $inputData): Result
    {
        $criteria = new PersonSearchCriteria(
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
