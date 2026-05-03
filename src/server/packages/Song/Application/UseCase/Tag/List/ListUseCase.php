<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Tag\List;

use AdminUser\Domain\Models\Permission;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\UseCaseError;

readonly class ListUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private SongTagRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<ListOutputData, UseCaseError>
     */
    public function handle(): Result
    {
        return $this->authorizer->require(Permission::ReadSong)
            ->andThen(fn () => $this->listSongTags());
    }

    /**
     * @return Result<ListOutputData, UseCaseError>
     */
    private function listSongTags(): Result
    {
        return new Ok(new ListOutputData($this->repository->all()));
    }
}
