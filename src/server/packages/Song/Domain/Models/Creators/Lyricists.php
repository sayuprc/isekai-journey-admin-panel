<?php

declare(strict_types=1);

namespace Song\Domain\Models\Creators;

use Creator\Domain\Models\CreatorId;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Domain\ValueObjects\OrderNo;

readonly class Lyricists
{
    /**
     * @param array<Lyricist> $lyricists
     */
    private function __construct(public array $lyricists)
    {
    }

    /**
     * @param array<array{creatorId: string, orderNo: int}> $items
     *
     * @return Result<self, string>
     */
    public static function fromArray(array $items): Result
    {
        $lyricists = [];

        foreach ($items as $item) {
            $result = Result::collect(
                CreatorId::create($item['creatorId']),
                OrderNo::create($item['orderNo']),
            )->map(fn (array $values) => new Lyricist(...$values));

            if ($result->isErr()) {
                return new Err('');
            }

            $lyricists[] = $result->unwrap();
        }

        return new Ok(new self($lyricists));
    }
}
