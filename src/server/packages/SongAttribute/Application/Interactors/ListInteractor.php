<?php

declare(strict_types=1);

namespace SongAttribute\Application\Interactors;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\SongAttribute;
use SongAttribute\Application\UseCase\List\ListOutputData;
use SongAttribute\Application\UseCase\List\ListUseCaseInterface;
use UseCaseError\AuthenticationError;
use UseCaseError\AuthorizationError;

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

        if (! $user->can(Permission::ReadSongAttribute)) {
            return new Err(new AuthorizationError());
        }

        return new Ok(new ListOutputData(SongAttribute::cases()));
    }
}
