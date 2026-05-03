<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Tag\Search;

use AdminUser\Domain\Models\Permission;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Criteria\Tag\SongTagSearchCriteria;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\UseCaseError;

readonly class SearchUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private SongTagRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    public function handle(SearchInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::ReadSong)
            ->andThen(fn () => $this->searchSongTags($inputData));
    }

    /**
     * @return Result<SearchOutputData, UseCaseError>
     */
    private function searchSongTags(SearchInputData $inputData): Result
    {
        $criteria = new SongTagSearchCriteria(
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
