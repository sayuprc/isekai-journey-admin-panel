<?php

declare(strict_types=1);

namespace Song\Application\Interactors\Tag;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use Override;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\UseCase\Tag\Delete\DeleteInputData;
use Song\Application\UseCase\Tag\Delete\DeleteUseCaseInterface;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(
        private AuthContext $context,
        private SongTagRepositoryInterface $repository,
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

        return SongTagId::create($inputData->songTagId)
            ->mapErr(fn (): UseCaseError => new InvalidInputError(['songTagId' => ['IDが不正です']]))
            ->andThen(function (SongTagId $songTagId): Result {
                $this->repository->delete($songTagId);

                return new Ok(null);
            });
    }
}
