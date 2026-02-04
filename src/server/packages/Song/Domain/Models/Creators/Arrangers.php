<?php

declare(strict_types=1);

namespace Song\Domain\Models\Creators;

use Creator\Domain\Models\CreatorId;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Domain\ValueObjects\OrderNo;

readonly class Arrangers
{
    /**
     * @param array<Arranger> $arrangers
     */
    private function __construct(public array $arrangers)
    {
    }

    /**
     * @param array<array{creatorId: string, orderNo: int}> $items
     *
     * @return Result<self, string>
     */
    public static function fromArray(array $items): Result
    {
        $arrangers = [];

        foreach ($items as $item) {
            $result = Result::collect(
                CreatorId::create($item['creatorId']),
                OrderNo::create($item['orderNo']),
            )->map(fn (array $values) => new Arranger(...$values));

            if ($result->isErr()) {
                return new Err('');
            }

            $arrangers[] = $result->unwrap();
        }

        return new Ok(new self($arrangers));
    }
}
