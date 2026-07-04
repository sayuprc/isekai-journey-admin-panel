<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Search;

use AdminUser\Domain\Models\Permission;
use Release\Application\Admin\Query\ReleaseGroupSearchQueryServiceInterface;
use Release\Domain\Criteria\ReleaseGroupSearchCriteria;
use Release\Domain\Models\ReleaseGroupType;
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
        private ReleaseGroupSearchQueryServiceInterface $query,
    ) {
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    public function handle(SearchInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::ReadRelease)
            ->andThen(fn () => $this->searchReleaseGroups($inputData));
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    private function searchReleaseGroups(SearchInputData $inputData): Result
    {
        $criteria = new ReleaseGroupSearchCriteria(
            $inputData->title === Arg::Optional
                ? new None()
                : new Some($inputData->title),
            $inputData->type === Arg::Optional
                ? new None()
                : new Some(ReleaseGroupType::from($inputData->type)),
            $inputData->isDisplay === Arg::Optional
                ? new None()
                : new Some($inputData->isDisplay),
            $inputData->page,
            $inputData->perPage,
        );

        return new Ok(new SearchOutputData(
            $this->query->search($criteria),
            $this->query->maxPage($criteria),
        ));
    }
}
