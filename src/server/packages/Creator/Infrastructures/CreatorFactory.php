<?php

declare(strict_types=1);

namespace Creator\Infrastructures;

use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;

readonly class CreatorFactory implements CreatorFactoryInterface
{
    public function create(CreatorId $creatorId, CreatorName $creatorName): Creator
    {
        return new Creator($creatorId, $creatorName);
    }
}
