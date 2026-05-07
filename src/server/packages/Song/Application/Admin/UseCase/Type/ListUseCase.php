<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Type;

use AdminUser\Domain\Models\Permission;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\SongType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\UseCaseError;

readonly class ListUseCase
{
    public function __construct(private UseCaseAuthorizer $authorizer)
    {
    }

    /**
     * @return Result<ListOutputData, UseCaseError>
     */
    public function handle(): Result
    {
        return $this->authorizer->require(Permission::ReadSong)
            ->andThen(fn () => $this->listSongTypes());
    }

    /**
     * @return Result<ListOutputData, UseCaseError>
     */
    private function listSongTypes(): Result
    {
        return new Ok(new ListOutputData(SongType::cases()));
    }
}
