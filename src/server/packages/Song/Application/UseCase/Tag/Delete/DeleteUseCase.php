<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Tag\Delete;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteUseCase
{
    public function __construct(
        private AuthContext $context,
        private SongTagRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<null, UseCaseError>
     */
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
                if ($this->repository->isUsed($songTagId)) {
                    return new Err(new BusinessLogicError('この楽曲タグは楽曲に使用されているため削除できません'));
                }

                $this->repository->delete($songTagId);

                return new Ok(null);
            });
    }
}
