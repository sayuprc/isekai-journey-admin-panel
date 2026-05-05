<?php

declare(strict_types=1);

namespace Media\Application\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Media\Domain\Criteria\MediaSearchCriteria;
use Media\Domain\Models\MediaRepositoryInterface;
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
        private MediaRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    public function handle(SearchInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::ReadSong)
            ->andThen(fn () => $this->searchMedia($inputData));
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    private function searchMedia(SearchInputData $inputData): Result
    {
        $criteria = new MediaSearchCriteria(
            $inputData->title === Arg::Optional
                ? new None()
                : new Some($inputData->title),
            $inputData->page,
            $inputData->perPage,
        );

        return new Ok(new SearchOutputData(
            $this->repository->search($criteria),
            $this->repository->maxPage($criteria),
        ));
    }
}
