<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use Creator\Application\UseCase\Delete\DeleteInputData;
use Creator\Application\UseCase\Delete\DeleteUseCaseInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Creator\Domain\Services\CreatorUsageCheckerInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;

readonly class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(
        private CreatorRepositoryInterface $repository,
        private CreatorUsageCheckerInterface $usageChecker,
    ) {
    }

    public function handle(DeleteInputData $inputData): Result
    {
        return CreatorId::create($inputData->creatorId)
            ->mapErr(fn (): string => 'IDが不正です')
            ->andThen(function (CreatorId $creatorId): Result {
                if ($this->usageChecker->isUsed($creatorId)) {
                    return new Err('このクリエイターは楽曲に使用されているため削除できません');
                }

                $this->repository->delete($creatorId);

                return new Ok(null);
            });
    }
}
