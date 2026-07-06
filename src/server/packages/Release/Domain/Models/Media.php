<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Collection\ImmutableCollection;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, Medium>
 */
readonly class Media extends ImmutableCollection
{
    /**
     * @param list<array{position: int, formatValue: int, tracks: list<array{songId: ?string, title: ?string, trackNo: int}>}> $items
     *
     * @return Result<self, DomainValidationError>
     */
    public static function fromArray(array $items): Result
    {
        $media = [];
        $seenPositions = [];

        foreach ($items as $item) {
            $positionResult = OrderNo::create($item['position']);

            if ($positionResult->isErr()) {
                $error = $positionResult->unwrapErr();

                return new Err(new DomainValidationError([$error->field => [$error->message]]));
            }

            $format = MediumFormat::tryFrom($item['formatValue']);

            if (is_null($format)) {
                return new Err(new DomainValidationError([
                    'media' => ["不正な媒体種別です: {$item['formatValue']}"],
                ]));
            }

            $tracksResult = Tracks::fromArray($item['tracks']);

            if ($tracksResult->isErr()) {
                return new Err($tracksResult->unwrapErr());
            }

            $medium = new Medium($positionResult->unwrap(), $format, $tracksResult->unwrap());

            if (isset($seenPositions[$medium->position->value])) {
                return new Err(new DomainValidationError([
                    'media' => ['同じ媒体順を複数指定することはできません。'],
                ]));
            }

            $seenPositions[$medium->position->value] = true;
            $media[] = $medium;
        }

        return new Ok(new self($media));
    }

    /**
     * @param list<array{position: int, format: int, tracks: list<array{songId: ?string, title: ?string, trackNo: int}>}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): Medium => Medium::reconstruct(
                $item['position'],
                $item['format'],
                $item['tracks'],
            ),
            $items,
        ));
    }

    /**
     * @return list<array{position: int, format: value-of<MediumFormat>, tracks: list<array{song_id: ?string, title: ?string, track_no: int}>}>
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
