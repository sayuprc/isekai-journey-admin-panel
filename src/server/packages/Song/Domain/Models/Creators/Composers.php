<?php

declare(strict_types=1);

namespace Song\Domain\Models\Creators;

use Creator\Domain\Models\CreatorId;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Domain\ValueObjects\OrderNo;

readonly class Composers
{
    /**
     * @param array<Composer> $composers
     */
    private function __construct(public array $composers)
    {
    }

    /**
     * @param array<array{creatorId: string, orderNo: int}> $items
     *
     * @return Result<self, string>
     */
    public static function fromArray(array $items): Result
    {
        $composers = [];

        foreach ($items as $item) {
            $result = Result::collect(
                CreatorId::create($item['creatorId']),
                OrderNo::create($item['orderNo']),
            )->map(fn (array $values) => new Composer(...$values));

            if ($result->isErr()) {
                return new Err('');
            }

            $composers[] = $result->unwrap();
        }

        return new Ok(new self($composers));
    }
}
