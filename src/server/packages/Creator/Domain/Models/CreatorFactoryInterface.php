<?php

declare(strict_types=1);

namespace Creator\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

interface CreatorFactoryInterface
{
    public function create(CreatorId $creatorId, CreatorName $name, OrderNo $orderNo): Creator;
}
