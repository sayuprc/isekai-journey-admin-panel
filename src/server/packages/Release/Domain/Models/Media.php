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
     * @param list<array{position: int, name: ?string, tracks: list<array{songId: ?string, title: ?string, trackNo: int}>}> $items
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

            $normalizedName = self::normalizeName($item['name']);
            $nameResult = is_null($normalizedName)
                ? new Ok(null)
                : MediumName::create($normalizedName);

            if ($nameResult->isErr()) {
                $error = $nameResult->unwrapErr();

                return new Err(new DomainValidationError([$error->field => [$error->message]]));
            }

            $tracksResult = Tracks::fromArray($item['tracks']);

            if ($tracksResult->isErr()) {
                return new Err($tracksResult->unwrapErr());
            }

            $medium = new Medium($positionResult->unwrap(), $nameResult->unwrap(), $tracksResult->unwrap());

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
     * @param list<array{position: int, name: ?string, tracks: list<array{songId: ?string, title: ?string, trackNo: int}>}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): Medium => Medium::reconstruct(
                $item['position'],
                $item['name'],
                $item['tracks'],
            ),
            $items,
        ));
    }

    /**
     * @return list<array{position: int, name: ?string, tracks: list<array{song_id: ?string, title: ?string, track_no: int}>}>
     */
    public function toArray(): array
    {
        $items = [];

        foreach ($this->toGeneric() as $item) {
            $items[] = $item->toArray();
        }

        return $items;
    }

    private static function normalizeName(?string $name): ?string
    {
        if (is_null($name)) {
            return null;
        }

        $trimmed = trim($name);

        return $trimmed === '' ? null : $trimmed;
    }
}
