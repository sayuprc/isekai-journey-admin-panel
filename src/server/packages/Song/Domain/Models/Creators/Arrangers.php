<?php

declare(strict_types=1);

namespace Song\Domain\Models\Creators;

use Creator\Domain\Models\CreatorId;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Collection\ImmutableCollection;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, Arranger>
 */
readonly class Arrangers extends ImmutableCollection
{
    /**
     * @param list<array{creatorId: string}> $items
     *
     * @return Result<self, string>
     */
    public static function fromArray(array $items): Result
    {
        $arrangers = [];

        foreach ($items as $index => $item) {
            $result = Result::collect(
                CreatorId::create($item['creatorId']),
                OrderNo::create($index + 1),
            )->map(fn (array $values) => new Arranger(...$values));

            if ($result->isErr()) {
                return new Err('');
            }

            $arrangers[] = $result->unwrap();
        }

        return new Ok(new self($arrangers));
    }

    /**
     * @param list<array{creatorId: string, orderNo: int}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(fn (array $item): Arranger => Arranger::reconstruct(...$item), $items));
    }

    /**
     * @return list<array{creator_id: string, order_no: int}>
     */
    public function toArray(): array
    {
        return $this->toGeneric()
            ->map(fn (Arranger $item): array => $item->toArray())
            ->toList();
    }
}
