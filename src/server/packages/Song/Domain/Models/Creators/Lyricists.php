<?php

declare(strict_types=1);

namespace Song\Domain\Models\Creators;

use Creator\Domain\Models\CreatorId;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Collection\ImmutableCollection;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, Lyricist>
 */
readonly class Lyricists extends ImmutableCollection
{
    /**
     * @param list<array{creatorId: string}> $items
     *
     * @return Result<self, DomainValidationError>
     */
    public static function fromArray(array $items): Result
    {
        $lyricists = [];

        foreach ($items as $index => $item) {
            $result = Result::collect(
                CreatorId::create($item['creatorId']),
                OrderNo::create($index + 1),
            )->map(fn (array $values) => new Lyricist(...$values));

            if ($result->isErr()) {
                $messages = [];
                foreach ($result->unwrapErr() as $error) {
                    if ($error instanceof EntityRuleViolationError) {
                        $messages[$error->field] ??= [];
                        $messages[$error->field][] = $error->message;
                    }
                }

                return new Err(new DomainValidationError($messages));
            }

            $lyricists[] = $result->unwrap();
        }

        return new Ok(new self($lyricists));
    }

    /**
     * @param list<array{creatorId: string, orderNo: int}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(fn (array $item): Lyricist => Lyricist::reconstruct(...$item), $items));
    }

    /**
     * @return list<array{creator_id: string, order_no: int}>
     */
    public function toArray(): array
    {
        return $this->toGeneric()
            ->map(fn (Lyricist $item): array => $item->toArray())
            ->toList();
    }
}
