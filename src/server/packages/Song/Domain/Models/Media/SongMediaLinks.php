<?php

declare(strict_types=1);

namespace Song\Domain\Models\Media;

use Media\Domain\Models\MediaId;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Collection\ImmutableCollection;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, SongMediaLink>
 */
readonly class SongMediaLinks extends ImmutableCollection
{
    /**
     * @param list<array{mediaId: string, orderNo: int}> $items
     *
     * @return Result<self, DomainValidationError>
     */
    public static function fromArray(array $items): Result
    {
        $links = [];
        $seen = [];

        foreach ($items as $item) {
            $result = Result::collect(
                MediaId::create($item['mediaId']),
                OrderNo::create($item['orderNo']),
            )->map(static fn (array $items): SongMediaLink => new SongMediaLink(...$items));

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

            $link = $result->unwrap();

            if (isset($seen[$link->mediaId->value])) {
                return new Err(new DomainValidationError([
                    'media' => ['同じメディアを複数指定することはできません。'],
                ]));
            }

            $seen[$link->mediaId->value] = true;
            $links[] = $link;
        }

        return new Ok(new self($links));
    }

    /**
     * @param list<array{mediaId: string, orderNo: int}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): SongMediaLink => SongMediaLink::reconstruct(
                $item['mediaId'],
                $item['orderNo'],
            ),
            $items,
        ));
    }

    /**
     * @return list<array{media_id: string, order_no: int}>
     */
    public function toArray(): array
    {
        $items = [];

        foreach ($this->toGeneric() as $item) {
            $items[] = $item->toArray();
        }

        return $items;
    }
}
