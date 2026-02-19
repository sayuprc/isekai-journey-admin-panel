<?php

declare(strict_types=1);

namespace Song\Application\Interactors;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\Assemble\SongAssembler;
use Song\Application\UseCase\List\ListOutputData;
use Song\Application\UseCase\List\ListUseCaseInterface;
use Song\Domain\Models\SongRepositoryInterface;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;

readonly class ListInteractor implements ListUseCaseInterface
{
    public function __construct(
        private AuthContext $context,
        private SongRepositoryInterface $repository,
        private SongAssembler $assembler,
    ) {
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

        return new Ok(new ListOutputData(array_map($this->assembler->assemble(...), $this->repository->all())));
    }
}
