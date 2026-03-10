<?php

declare(strict_types=1);

namespace Song\Application\Interactors;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use Override;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\UseCase\ListAttribute\ListAttributeOutputData;
use Song\Application\UseCase\ListAttribute\ListAttributeUseCaseInterface;
use Song\Domain\Models\SongAttribute;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;

readonly class ListAttributeInteractor implements ListAttributeUseCaseInterface
{
    public function __construct(private AuthContext $context)
    {
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

        return new Ok(new ListAttributeOutputData(SongAttribute::cases()));
    }
}
