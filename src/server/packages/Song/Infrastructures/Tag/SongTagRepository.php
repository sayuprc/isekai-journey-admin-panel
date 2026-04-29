<?php

declare(strict_types=1);

namespace Song\Infrastructures\Tag;

use App\Models\Song\SongTag as ModelsSongTag;
use Override;
use Song\Domain\Criteria\Tag\SongTagSearchCriteria;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\SqlHelper;

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
    public function search(SongTagSearchCriteria $criteria): array
    {
        $query = ModelsSongTag::query();

        if ($criteria->name->isPresent()) {
            $keyword = SqlHelper::escapeLike(mb_strtolower($criteria->name->get()));
            $query = $query->whereLike('name_lower', $keyword . '%');
        }

        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        return $query->orderBy($criteria->sort->value, $criteria->order->value)
            ->limit($criteria->perPage->value)
            ->offset($offset)
            ->get()
            ->map($this->hydrate(...))
            ->all();
    }

    #[Override]
    public function maxPage(SongTagSearchCriteria $criteria): int
    {
        $query = ModelsSongTag::query();

        if ($criteria->name->isPresent()) {
            $keyword = SqlHelper::escapeLike(mb_strtolower($criteria->name->get()));
            $query = $query->whereLike('name_lower', $keyword . '%');
        }

        return (int)ceil($query->count() / $criteria->perPage->value);
    }

    #[Override]
    public function find(SongTagId $songTagId): ?SongTag
    {
        $found = ModelsSongTag::query()
            ->where('song_tag_id', $this->converter->toBin($songTagId->value))
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
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
    public function delete(SongTagId $songTagId): void
    {
        ModelsSongTag::query()
            ->where('song_tag_id', $this->converter->toBin($songTagId->value))
            ->delete();
    }

    #[Override]
    public function getMaxOrderNo(): int
    {
        /** @var int */
        return ModelsSongTag::query()->max('order_no') ?? 0;
    }

    private function hydrate(ModelsSongTag $model): SongTag
    {
        return SongTag::reconstruct(
            $this->converter->toUuid($model->song_tag_id),
            $model->name,
            $model->order_no,
        );
    }
}
