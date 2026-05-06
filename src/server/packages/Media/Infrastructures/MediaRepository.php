<?php

declare(strict_types=1);

namespace Media\Infrastructures;

use App\Models\Media\Media as ModelsMedia;
use Illuminate\Database\Eloquent\Builder;
use Media\Domain\Criteria\MediaSearchCriteria;
use Media\Domain\Models\Media;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Domain\Models\MediaUrl;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\SqlHelper;

readonly class MediaRepository implements MediaRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function find(MediaId $mediaId): ?Media
    {
        $found = ModelsMedia::query()
            ->where('media_id', $this->converter->toBin($mediaId->value))
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    #[Override]
    public function findByUrl(MediaUrl $url): ?Media
    {
        $found = ModelsMedia::query()
            ->where('url', $url->value)
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    #[Override]
    public function isUsed(MediaId $mediaId): bool
    {
        return ModelsMedia::query()
            ->where('media_id', $this->converter->toBin($mediaId->value))
            ->whereHas('songMediaLinks')
            ->exists();
    }

    #[Override]
    public function search(MediaSearchCriteria $criteria): array
    {
        $query = $this->buildSearchQuery($criteria);
        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        return array_values($query
            ->orderBy('title')
            ->limit($criteria->perPage->value)
            ->offset($offset)
            ->get()
            ->map($this->hydrate(...))
            ->all());
    }

    #[Override]
    public function maxPage(MediaSearchCriteria $criteria): int
    {
        $count = $this->buildSearchQuery($criteria)->count();

        return (int)ceil($count / $criteria->perPage->value);
    }

    #[Override]
    public function findByIds(MediaId ...$mediaIds): array
    {
        return array_values(ModelsMedia::query()
            ->whereIn(
                'media_id',
                array_map(fn (MediaId $mediaId): string => $this->converter->toBin($mediaId->value), $mediaIds),
            )
            ->get()
            ->map($this->hydrate(...))
            ->all());
    }

    #[Override]
    public function save(Media $media): Media
    {
        ModelsMedia::query()->upsert(
            [
                ...$media->toArray(),
                'media_id' => $this->converter->toBin($media->mediaId->value),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            ['media_id'],
            [
                'title',
                'url',
                'type',
                'format',
                'is_display',
                'updated_at',
            ],
        );

        return $media;
    }

    #[Override]
    public function delete(MediaId $mediaId): void
    {
        ModelsMedia::query()
            ->where('media_id', $this->converter->toBin($mediaId->value))
            ->delete();
    }

    /**
     * @return Builder<ModelsMedia>
     */
    private function buildSearchQuery(MediaSearchCriteria $criteria)
    {
        $query = ModelsMedia::query();

        if ($criteria->title->isPresent()) {
            $keyword = SqlHelper::escapeLike($criteria->title->get());
            $query = $query->whereLike('title', '%' . $keyword . '%');
        }

        if ($criteria->type->isPresent()) {
            $query = $query->where('type', $criteria->type->get()->value);
        }

        if ($criteria->format->isPresent()) {
            $query = $query->where('format', $criteria->format->get()->value);
        }

        if ($criteria->isDisplay->isPresent()) {
            $query = $query->where('is_display', $criteria->isDisplay->get());
        }

        return $query;
    }

    private function hydrate(ModelsMedia $row): Media
    {
        return Media::reconstruct(
            $this->converter->toUuid($row->media_id),
            $row->title,
            $row->url,
            $row->type,
            $row->format,
            $row->is_display,
        );
    }
}
