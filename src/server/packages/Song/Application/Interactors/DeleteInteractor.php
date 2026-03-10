<?php

declare(strict_types=1);

namespace Song\Application\Interactors;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\UseCase\Delete\DeleteInputData;
use Song\Application\UseCase\Delete\DeleteUseCaseInterface;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;
use Override;

readonly class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(
        private AuthContext $context,
        private SongRepositoryInterface $repository,
    ) {
    }

    #[Override]
    public function handle(DeleteInputData $inputData): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::WriteSong)) {
            return new Err(new AuthorizationError());
        }

        return SongId::create($inputData->songId)
            ->mapErr(fn (): UseCaseError => new InvalidInputError(['songId' => ['IDが不正です']]))
            ->andThen(function (SongId $songId): Result {
                $this->repository->delete($songId);

                return new Ok(null);
            });
    }
}
