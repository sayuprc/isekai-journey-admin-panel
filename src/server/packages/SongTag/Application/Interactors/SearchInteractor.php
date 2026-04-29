<?php

declare(strict_types=1);

namespace SongTag\Application\Interactors;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use Override;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\TagRepositoryInterface;
use SongTag\Application\UseCase\Search\SearchInputData;
use SongTag\Application\UseCase\Search\SearchOutputData;
use SongTag\Application\UseCase\Search\SearchUseCaseInterface;
use SongTag\Domain\Criteria\TagSearchCriteria;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;

readonly class SearchInteractor implements SearchUseCaseInterface
{
    public function __construct(
        private AuthContext $context,
        private TagRepositoryInterface $repository,
    ) {
    }

    #[Override]
    public function handle(SearchInputData $inputData): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::ReadSong)) {
            return new Err(new AuthorizationError());
        }

        $criteria = new TagSearchCriteria(
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
