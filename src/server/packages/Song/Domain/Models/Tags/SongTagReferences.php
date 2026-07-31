<?php

declare(strict_types=1);

namespace Song\Domain\Models\Tags;

use Song\Domain\Models\Tag\SongTagId;
use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\DomainValidationException;
use Support\Domain\Exceptions\InvalidDomainException;

/**
 * @extends ImmutableCollection<int, SongTagReference>
 */
readonly class SongTagReferences extends ImmutableCollection
{
    /**
     * @param list<array{songTagId: string}> $items
     *
     * @throws DomainValidationException
     */
    public static function fromArray(array $items): self
    {
        $tags = [];
        $seen = [];

        foreach ($items as $item) {
            try {
                $tag = new SongTagReference(new SongTagId($item['songTagId']));
            } catch (InvalidDomainException $e) {
                throw new DomainValidationException(['songTagId' => [$e->getMessage()]]);
            }

            if (isset($seen[$tag->songTagId->value])) {
                throw new DomainValidationException([
                    'songTagId' => ['同じ楽曲タグを複数指定することはできません。'],
                ]);
            }

            $seen[$tag->songTagId->value] = true;
            $tags[] = $tag;
        }

        return new self($tags);
    }

    /**
     * @param list<array{songTagId: string}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(static fn (array $item): SongTagReference => SongTagReference::reconstruct(...$item), $items));
    }

    /**
     * @return list<array{song_tag_id: string}>
     */
    public function toArray(): array
    {
        return $this->toGeneric()
            ->map(static fn (SongTagReference $item): array => $item->toArray())
            ->toList();
    }
}
