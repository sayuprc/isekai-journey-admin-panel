<?php

declare(strict_types=1);

namespace Song\Infrastructures;

use App\Models\Song\SongTag as ModelsSongTag;
use Override;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagName;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class SongTagRepository implements SongTagRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function all(): array
    {
        return ModelsSongTag::query()
            ->orderBy('order_no')
            ->get()
            ->map($this->hydrate(...))
            ->all();
    }

    #[Override]
    public function findByName(SongTagName $name): ?SongTag
    {
        $found = ModelsSongTag::query()
            ->where('name', $name->value)
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    #[Override]
    public function save(SongTag $tag): SongTag
    {
        ModelsSongTag::query()->upsert(
            [
                ...$tag->toArray(),
                'song_tag_id' => $this->converter->toBin($tag->songTagId->value),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            ['song_tag_id'],
            [
                'name',
                'order_no',
                'updated_at',
            ],
        );

        return $tag;
    }

    #[Override]
    public function getMaxOrderNo(): int
    {
        /** @var int */
        return ModelsSongTag::query()->max('order_no') ?? 0;
    }

    private function hydrate(ModelsSongTag $row): SongTag
    {
        return SongTag::reconstruct(
            $this->converter->toUuid($row->song_tag_id),
            $row->name,
            $row->order_no,
        );
    }
}
