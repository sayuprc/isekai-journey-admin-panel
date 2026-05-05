<?php

declare(strict_types=1);

namespace Media\Application\UseCase\Search;

use AdminUser\Domain\Models\Permission;
use Media\Domain\Criteria\MediaSearchCriteria;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Domain\Models\MediaType;
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
        return $this->authorizer->require(Permission::ReadMedia)
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
            $inputData->type === Arg::Optional
                ? new None()
                : new Some(MediaType::from($inputData->type)),
            $inputData->format === Arg::Optional
                ? new None()
                : new Some(MediaFormat::from($inputData->format)),
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
