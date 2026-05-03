<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorUsageCheckerInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\BusinessLogicError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private CreatorRepositoryInterface $repository,
        private CreatorUsageCheckerInterface $usageChecker,
    ) {
    }

    /**
     * @return Result<null, UseCaseError>
     */
    public function handle(DeleteInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WriteCreator)
            ->andThen(fn () => $this->deleteCreator($inputData));
    }

    /**
     * @return Result<null, UseCaseError>
     */
    private function deleteCreator(DeleteInputData $inputData): Result
    {
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
