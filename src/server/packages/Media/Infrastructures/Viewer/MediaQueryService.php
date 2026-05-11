<?php

declare(strict_types=1);

namespace Media\Infrastructures\Viewer;

use App\Models\Media\Media;
use Illuminate\Database\Eloquent\Builder;
use Media\Application\Viewer\Query\MediaListCursor;
use Media\Application\Viewer\Query\MediaListItem;
use Media\Application\Viewer\Query\MediaListPage;
use Media\Application\Viewer\Query\MediaQueryServiceInterface;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class MediaQueryService implements MediaQueryServiceInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function list(?string $cursor, int $limit): MediaListPage
    {
        $query = Media::query()
            ->select(['media_id', 'title', 'url', 'published_at', 'type', 'format'])
            ->where('is_display', true)
            ->orderBy('published_at', 'desc')
            ->orderBy('media_id');

        if (is_string($cursor)) {
            $decoded = MediaListCursor::decode($cursor);

            $query->where(
                fn (Builder $builder) => $builder
                    ->where('published_at', '<', $decoded->publishedAt)
                    ->orWhere(fn (Builder $sameDate) => $sameDate
                        ->where('published_at', $decoded->publishedAt)
                        ->where('media_id', '>', $this->converter->toBin($decoded->mediaId))),
            );
        }

        $media = $query
            ->limit($limit + 1)
            ->get()
            ->map(fn (Media $row): MediaListItem => new MediaListItem(
                $this->converter->toUuid($row->media_id),
                $row->title,
                $row->url,
                $row->published_at->toDateTimeImmutable(),
                MediaType::from($row->type),
                MediaFormat::from($row->format),
            ));

        $hasNextPage = $media->count() > $limit;
        $currentMedia = $hasNextPage ? $media->slice(0, $limit) : $media;
        $lastMedia = $hasNextPage ? $currentMedia->last() : null;

        $nextCursor = is_null($lastMedia)
            ? null
            : MediaListCursor::encode($lastMedia->publishedAt->format('Y-m-d'), $lastMedia->mediaId);

        return new MediaListPage($currentMedia->all(), $nextCursor);
    }
}
