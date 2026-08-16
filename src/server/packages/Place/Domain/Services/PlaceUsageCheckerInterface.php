<?php

declare(strict_types=1);

namespace Place\Domain\Services;

use Place\Domain\Models\PlaceId;

interface PlaceUsageCheckerInterface
{
    public function isUsed(PlaceId $placeId): bool;
}
