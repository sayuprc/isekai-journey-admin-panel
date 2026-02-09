<?php

declare(strict_types=1);

namespace Song\Infrastructures;

use Creator\Domain\Models\CreatorId;
use Creator\Domain\Services\CreatorUsageCheckerInterface;
use Song\Domain\Models\SongRepositoryInterface;

readonly class CreatorUsageChecker implements CreatorUsageCheckerInterface
{
    public function __construct(private SongRepositoryInterface $repository)
    {
    }

    public function isUsed(CreatorId $creatorId): bool
    {
        return $this->repository->isCreatorUsed($creatorId);
    }
}
