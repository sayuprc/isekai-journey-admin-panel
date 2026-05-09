<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Release\Domain\Criteria\ReleaseSearchCriteria;
use Release\Domain\Models\ReleaseDistributionType;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Release\Domain\Models\ReleaseType;
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
        private ReleaseRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    public function handle(SearchInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::ReadRelease)
            ->andThen(fn () => $this->searchReleases($inputData));
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    private function searchReleases(SearchInputData $inputData): Result
    {
        $criteria = new ReleaseSearchCriteria(
            $inputData->title === Arg::Optional
                ? new None()
                : new Some($inputData->title),
            $inputData->type === Arg::Optional
                ? new None()
                : new Some(ReleaseType::from($inputData->type)),
            $inputData->distributionType === Arg::Optional
                ? new None()
                : new Some(ReleaseDistributionType::from($inputData->distributionType)),
            $inputData->isDisplay === Arg::Optional
                ? new None()
                : new Some($inputData->isDisplay),
            $inputData->page,
            $inputData->perPage,
        );

        return new Ok(new SearchOutputData(
            $this->repository->search($criteria),
            $this->repository->maxPage($criteria),
        ));
    }
}
