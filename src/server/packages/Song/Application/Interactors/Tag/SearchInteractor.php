<?php

declare(strict_types=1);

namespace Song\Application\Interactors\Tag;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use Override;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\Query\SongTagQueryServiceInterface;
use Song\Application\UseCase\Tag\Search\SearchInputData;
use Song\Application\UseCase\Tag\Search\SearchOutputData;
use Song\Application\UseCase\Tag\Search\SearchUseCaseInterface;
use Song\Domain\Criteria\SongTagSearchCriteria;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;

readonly class SearchInteractor implements SearchUseCaseInterface
{
    public function __construct(
        private AuthContext $context,
        private SongTagQueryServiceInterface $query,
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

        $criteria = new SongTagSearchCriteria(
            $inputData->name === Arg::Optional
                ? new None()
                : new Some($inputData->name),
            $inputData->sort,
            $inputData->order,
            $inputData->page,
            $inputData->perPage,
        );

        return new Ok(new SearchOutputData(
            $this->query->search($criteria),
            $this->query->maxPage($criteria),
        ));
    }
}
