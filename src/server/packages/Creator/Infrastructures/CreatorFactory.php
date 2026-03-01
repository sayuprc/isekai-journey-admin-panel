<?php

declare(strict_types=1);

namespace Creator\Infrastructures;

use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorFactoryInterface;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Support\Domain\ValueObjects\OrderNo;

readonly class CreatorFactory implements CreatorFactoryInterface
{
    public function create(CreatorId $creatorId, CreatorName $name, OrderNo $orderNo): Creator
    {
        return new Creator($creatorId, $name, $orderNo);
    }
}
