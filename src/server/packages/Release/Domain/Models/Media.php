<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\DomainValidationException;
use Support\Domain\Validation\FieldErrors;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, Medium>
 */
readonly class Media extends ImmutableCollection
{
    /**
     * @param list<array{position: int, name: ?string, tracks: list<array{songId: ?string, title: ?string, trackNo: int}>}> $items
     *
     * @throws DomainValidationException
     */
    public static function fromArray(array $items): self
    {
        $media = [];
        $seenPositions = [];

        foreach ($items as $item) {
            $position = FieldErrors::single('position', static fn (): OrderNo => new OrderNo($item['position']));

            $normalizedName = self::normalizeName($item['name']);
            $name = is_null($normalizedName)
                ? null
                : FieldErrors::single('name', static fn (): MediumName => new MediumName($normalizedName));

            $medium = new Medium($position, $name, Tracks::fromArray($item['tracks']));

            if (isset($seenPositions[$medium->position->value])) {
                throw new DomainValidationException([
                    'media' => ['同じ媒体順を複数指定することはできません。'],
                ]);
            }

            $seenPositions[$medium->position->value] = true;
            $media[] = $medium;
        }

        return new self($media);
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
