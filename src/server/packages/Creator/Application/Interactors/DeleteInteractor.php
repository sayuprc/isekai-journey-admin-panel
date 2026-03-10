<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use Override;
use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use Creator\Application\UseCase\Delete\DeleteInputData;
use Creator\Application\UseCase\Delete\DeleteUseCaseInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorUsageCheckerInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(
        private AuthContext $context,
        private CreatorRepositoryInterface $repository,
        private CreatorUsageCheckerInterface $usageChecker,
    ) {
    }

    #[Override]
    public function handle(DeleteInputData $inputData): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::WriteCreator)) {
            return new Err(new AuthorizationError());
        }

        return CreatorId::create($inputData->creatorId)
            ->mapErr(fn (): UseCaseError => new InvalidInputError(['creatorId' => ['IDが不正です']]))
            ->andThen(function (CreatorId $creatorId): Result {
                if ($this->usageChecker->isUsed($creatorId)) {
                    return new Err(new BusinessLogicError('このクリエイターは楽曲に使用されているため削除できません'));
                }

                $this->repository->delete($creatorId);

                return new Ok(null);
            });
    }
}
