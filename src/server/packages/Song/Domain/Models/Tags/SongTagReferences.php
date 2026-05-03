<?php

declare(strict_types=1);

namespace Song\Domain\Models\Tags;

use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\Tag\SongTagId;
use Support\Collection\ImmutableCollection;
use Support\Domain\Error\DomainValidationError;

/**
 * @extends ImmutableCollection<int, SongTagReference>
 */
readonly class SongTagReferences extends ImmutableCollection
{
    /**
     * @param list<array{songTagId: string}> $items
     *
     * @return Result<self, DomainValidationError>
     */
    public static function fromArray(array $items): Result
    {
        $tags = [];
        $seen = [];

        foreach ($items as $item) {
            $result = SongTagId::create($item['songTagId'])
                ->map(fn (SongTagId $songTagId) => new SongTagReference($songTagId));

            if ($result->isErr()) {
                $messages = [];
                $error = $result->unwrapErr();
                $messages[$error->field] = [];
                $messages[$error->field][] = $error->message;

                return new Err(new DomainValidationError($messages));
            }

            $tag = $result->unwrap();

            if (isset($seen[$tag->songTagId->value])) {
                return new Err(new DomainValidationError([
                    'songTagId' => ['同じ楽曲タグを複数指定することはできません。'],
                ]));
            }

            $seen[$tag->songTagId->value] = true;
            $tags[] = $tag;
        }

        return new Ok(new self($tags));
    }

    /**
     * @param list<array{songTagId: string}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(fn (array $item): SongTagReference => SongTagReference::reconstruct(...$item), $items));
    }

    /**
     * @return list<array{song_tag_id: string}>
     */
    public function toArray(): array
    {
        return $this->toGeneric()
            ->map(fn (SongTagReference $item): array => $item->toArray())
            ->toList();
    }
}
