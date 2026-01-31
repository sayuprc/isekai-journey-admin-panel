<?php

declare(strict_types=1);

namespace Performer\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

interface PerformerFactoryInterface
{
    public function create(PerformerId $performerId, PerformerName $performerName, OrderNo $orderNo): Performer;
}
