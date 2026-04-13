<?php

declare(strict_types=1);

namespace Song\Domain\Services;

use Song\Domain\Models\Tag\SongTagId;

interface SongTagUsageCheckerInterface
{
    public function isUsed(SongTagId $songTagId): bool;
}
