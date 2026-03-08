<?php

declare(strict_types=1);

namespace Song\Application\Interactors;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\UseCase\ListType\ListTypeOutputData;
use Song\Application\UseCase\ListType\ListTypeUseCaseInterface;
use Song\Domain\Models\SongType;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;

readonly class ListTypeInteractor implements ListTypeUseCaseInterface
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

        if (! $user->can(Permission::ReadSong)) {
            return new Err(new AuthorizationError());
        }

        return new Ok(new ListTypeOutputData(SongType::cases()));
    }
}
