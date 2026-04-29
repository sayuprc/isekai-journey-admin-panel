<?php

declare(strict_types=1);

namespace Song\Infrastructures\Tag;

use App\Models\Song\SongTag as ModelsSongTag;
use Override;
use Song\Domain\Criteria\Tag\SongTagSearchCriteria;
use Song\Domain\Models\Tag\SongTag;
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
    public function save(SongTag $songTag): SongTag
    {
        ModelsSongTag::query()->upsert(
            [
                ...$songTag->toArray(),
                'song_tag_id' => $this->converter->toBin($songTag->songTagId->value),
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

        return $songTag;
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
