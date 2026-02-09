<?php

declare(strict_types=1);

namespace Creator\Domain\Services;

use Creator\Domain\Models\CreatorId;

interface CreatorUsageCheckerInterface
{
    public function isUsed(CreatorId $creatorId): bool;
}
