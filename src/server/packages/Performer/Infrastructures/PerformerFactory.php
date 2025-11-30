<?php

declare(strict_types=1);

namespace Performer\Infrastructures;

use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerFactoryInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Support\Contracts\UuidGeneratorInterface;
use Support\Domain\ValueObjects\OrderNo;

readonly class PerformerFactory implements PerformerFactoryInterface
{
    public function __construct(private UuidGeneratorInterface $uuid)
    {
    }

    public function create(string $performerName, int $orderNo): Performer
    {
        return new Performer(
            new PerformerId($this->uuid->generate()),
            new PerformerName($performerName),
            new OrderNo($orderNo),
        );
    }

    public function reconstitute(string $performerId, string $performerName, int $orderNo): Performer
    {
        return new Performer(
            new PerformerId($performerId),
            new PerformerName($performerName),
            new OrderNo($orderNo),
        );
    }
}
