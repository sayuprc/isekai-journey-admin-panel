<?php

declare(strict_types=1);

namespace SongTag\Infrastructures;

use App\Models\Song\Tag as ModelsTag;
use Override;
use Song\Domain\Models\Tag;
use Song\Domain\Models\TagName;
use Song\Domain\Models\TagRepositoryInterface;
use SongTag\Domain\Criteria\TagSearchCriteria;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\SqlHelper;

readonly class TagRepository implements TagRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function all(): array
    {
        return ModelsTag::query()
            ->orderBy('order_no')
            ->get()
            ->map($this->hydrate(...))
            ->all();
    }

    #[Override]
    public function search(TagSearchCriteria $criteria): array
    {
        $query = ModelsTag::query();

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
    public function maxPage(TagSearchCriteria $criteria): int
    {
        $query = ModelsTag::query();

        if ($criteria->name->isPresent()) {
            $keyword = SqlHelper::escapeLike(mb_strtolower($criteria->name->get()));
            $query = $query->whereLike('name_lower', $keyword . '%');
        }

        return (int)ceil($query->count() / $criteria->perPage->value);
    }

    #[Override]
    public function findByName(TagName $name): ?Tag
    {
        $found = ModelsTag::query()
            ->where('name', $name->value)
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    #[Override]
    public function getMaxOrderNo(): int
    {
        /** @var int */
        return ModelsTag::query()->max('order_no') ?? 0;
    }

    #[Override]
    public function save(Tag $tag): Tag
    {
        $songTagId = $this->converter->toBin($tag->tagId->value);

        if (ModelsTag::query()->where('song_tag_id', $songTagId)->exists()) {
            ModelsTag::query()
                ->where('song_tag_id', $songTagId)
                ->update([
                    'name' => $tag->name->value,
                    'order_no' => $tag->orderNo->value,
                    'updated_at' => now(),
                ]);

            return $tag;
        }

        ModelsTag::query()->insert([
            'song_tag_id' => $songTagId,
            'name' => $tag->name->value,
            'order_no' => $tag->orderNo->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $tag;
    }

    private function hydrate(ModelsTag $row): Tag
    {
        return Tag::reconstruct(
            $this->converter->toUuid($row->song_tag_id),
            $row->name,
            $row->order_no,
        );
    }
}
