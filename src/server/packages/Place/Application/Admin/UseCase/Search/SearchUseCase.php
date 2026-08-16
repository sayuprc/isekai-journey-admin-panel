<?php

declare(strict_types=1);

namespace Place\Application\Admin\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Place\Domain\Criteria\PlaceSearchCriteria;
use Place\Domain\Models\PlaceKind;
use Place\Domain\Models\PlaceRepositoryInterface;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class SearchUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private PlaceRepositoryInterface $repository,
    ) {
    }

    public function handle(SearchInputData $inputData): SearchOutputData
    {
        $this->authorizer->authorize(Permission::ReadPlace);

        $criteria = new PlaceSearchCriteria(
            $inputData->name === Arg::Optional
                ? new None()
                : new Some($inputData->name),
            $inputData->kindValue === Arg::Optional
                ? new None()
                : new Some(PlaceKind::from($inputData->kindValue)),
            $inputData->sort,
            $inputData->order,
            $inputData->page,
            $inputData->perPage,
        );

        return new SearchOutputData(
            $this->repository->search($criteria),
            $this->repository->maxPage($criteria),
        );
    }
}
