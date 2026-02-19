<?php

declare(strict_types=1);

namespace SongType\Application\Interactors;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use SongType\Application\UseCase\List\ListOutputData;
use SongType\Application\UseCase\List\ListUseCaseInterface;
use SongType\Domain\Models\SongType;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;

readonly class ListInteractor implements ListUseCaseInterface
{
    public function __construct(private AuthContext $context)
    {
    }

    public function handle(): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::ReadSongType)) {
            return new Err(new AuthorizationError());
        }

        return new Ok(new ListOutputData(SongType::cases()));
    }
}
