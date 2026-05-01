<?php

declare(strict_types=1);

namespace Song\Application\Interactors\Tag;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use Override;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\UseCase\ListTag\ListTagOutputData;
use Song\Application\UseCase\ListTag\ListTagUseCaseInterface;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;

readonly class ListTagInteractor implements ListTagUseCaseInterface
{
    public function __construct(
        private AuthContext $context,
        private SongTagRepositoryInterface $repository,
    ) {
    }

    #[Override]
    public function handle(): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::ReadSong)) {
            return new Err(new AuthorizationError());
        }

        return new Ok(new ListTagOutputData($this->repository->all()));
    }
}
