<?php

declare(strict_types=1);

namespace Performer\Infrastructures;

use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerFactoryInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use ResultType\Result;
use Support\Contracts\UuidGeneratorInterface;
use Support\Domain\ValueObjects\OrderNo;

readonly class PerformerFactory implements PerformerFactoryInterface
{
    public function __construct(private UuidGeneratorInterface $uuid)
    {
    }

    public function create(string $performerName, int $orderNo): Result
    {
        return Result::collect3(
            PerformerId::create($this->uuid->generate()),
            PerformerName::create($performerName),
            OrderNo::create($orderNo),
        )->map(fn (array $values): Performer => new Performer(...$values))
            ->mapErr(fn (array $errors): array => array_filter($errors, fn ($item) => ! is_null($item)));
    }

    public function reconstitute(string $performerId, string $performerName, int $orderNo): Result
    {
        return Result::collect3(
            PerformerId::create($performerId),
            PerformerName::create($performerName),
            OrderNo::create($orderNo),
        )->map(fn (array $values): Performer => new Performer(...$values))
            ->mapErr(fn (array $errors): array => array_filter($errors, fn ($item) => ! is_null($item)));
    }
}
