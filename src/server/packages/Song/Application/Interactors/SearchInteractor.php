<?php

declare(strict_types=1);

namespace Song\Application\Interactors;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\Query\SongQueryServiceInterface;
use Song\Application\UseCase\Search\SearchInputData;
use Song\Application\UseCase\Search\SearchOutputData;
use Song\Application\UseCase\Search\SearchUseCaseInterface;
use Song\Domain\Criteria\SongSearchCriteria;
use Song\Domain\Models\SongAttribute;
use Song\Domain\Models\SongType;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Override;

readonly class SearchInteractor implements SearchUseCaseInterface
{
    public function __construct(
        private AuthContext $context,
        private SongQueryServiceInterface $query,
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

        $criteria = new SongSearchCriteria(
            $inputData->title === Arg::Optional
                ? new None()
                : new Some($inputData->title),
            $inputData->type === Arg::Optional
                ? new None()
                : new Some(SongType::from($inputData->type)),
            $inputData->attribute === Arg::Optional
                ? new None()
                : new Some(SongAttribute::from($inputData->attribute)),
            $inputData->sort,
            $inputData->order,
            $inputData->page,
            $inputData->perPage,
        );

        return new Ok(
            new SearchOutputData(
                $this->query->search($criteria),
                $this->query->maxPage($criteria),
            ),
        );
    }
}
