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
 * @extends ImmutableCollection<int, Composer>
 */
readonly class Composers extends ImmutableCollection
{
    /**
     * @param list<array{creatorId: string}> $items
     *
     * @return Result<self, string>
     */
    public static function fromArray(array $items): Result
    {
        $composers = [];

        foreach ($items as $index => $item) {
            $result = Result::collect(
                CreatorId::create($item['creatorId']),
                OrderNo::create($index + 1),
            )->map(fn (array $values) => new Composer(...$values));

            if ($result->isErr()) {
                return new Err('');
            }

            $composers[] = $result->unwrap();
        }

        return new Ok(new self($composers));
    }
}
