<?php

declare(strict_types=1);

namespace Performer\Infrastructures;

use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerFactoryInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Support\Domain\ValueObjects\OrderNo;
use Override;

readonly class PerformerFactory implements PerformerFactoryInterface
{
    #[Override]
    public function create(PerformerId $performerId, PerformerName $name, OrderNo $orderNo): Performer
    {
        return new Performer($performerId, $name, $orderNo);
    }
}
