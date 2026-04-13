<?php

declare(strict_types=1);

namespace Song\Infrastructures;

use Override;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Services\SongTagUsageCheckerInterface;

readonly class SongTagUsageChecker implements SongTagUsageCheckerInterface
{
    public function __construct(private SongRepositoryInterface $repository)
    {
    }

    #[Override]
    public function isUsed(SongTagId $songTagId): bool
    {
        return $this->repository->isSongTagUsed($songTagId);
    }
}
