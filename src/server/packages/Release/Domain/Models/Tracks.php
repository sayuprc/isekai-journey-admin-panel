<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\SongId;
use Support\Collection\ImmutableCollection;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, Track>
 */
readonly class Tracks extends ImmutableCollection
{
    /**
     * @param list<array{songId: string, trackNo: int}> $items
     *
     * @return Result<self, DomainValidationError>
     */
    public static function fromArray(array $items): Result
    {
        $tracks = [];
        $seenTrackNos = [];

        foreach ($items as $item) {
            $result = Result::collect(
                SongId::create($item['songId']),
                OrderNo::create($item['trackNo']),
            )->map(fn (array $values): Track => new Track(...$values));

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

            $track = $result->unwrap();

            if (isset($seenTrackNos[$track->trackNo->value])) {
                return new Err(new DomainValidationError([
                    'media' => ['同じ曲順を複数指定することはできません。'],
                ]));
            }

            $seenTrackNos[$track->trackNo->value] = true;
            $tracks[] = $track;
        }

        return new Ok(new self($tracks));
    }

    /**
     * @param list<array{songId: string, trackNo: int}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): Track => Track::reconstruct(
                $item['songId'],
                $item['trackNo'],
            ),
            $items,
        ));
    }

    /**
     * @return list<array{song_id: string, track_no: int}>
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
