<?php

declare(strict_types=1);

namespace Media\Infrastructures\Viewer;

use App\Models\Media\Media;
use App\Models\Song\SongMediaLink;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Media\Application\Viewer\Query\MediaListCursor;
use Media\Application\Viewer\Query\MediaListItem;
use Media\Application\Viewer\Query\MediaListPage;
use Media\Application\Viewer\Query\MediaQueryServiceInterface;
use Media\Application\Viewer\Query\MediaSongSummary;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaType;
use Override;
use Song\Domain\Models\SongType;
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
            // 将来的に Eloquent やめるので黙らせる
            // @phpstan-ignore-next-line
            ->with([
                'songMediaLinks' => fn (HasMany $query) => $query
                    ->select(['media_id', 'song_id', 'order_no'])
                    ->whereHas('song', fn (Builder $songQuery) => $songQuery->where('is_display', true))
                    ->orderBy('order_no'),
                'songMediaLinks.song' => fn (BelongsTo $query) => $query
                    ->select(['song_id', 'title', 'type']),
            ])
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
            ->map(function (Media $row): MediaListItem {
                $songs = $row->songMediaLinks
                    ->toBase()
                    ->map(fn (SongMediaLink $link): MediaSongSummary => new MediaSongSummary(
                        $this->converter->toUuid($link->song->song_id),
                        $link->song->title,
                        SongType::from($link->song->type),
                    ))
                    ->values()
                    ->all();

                return new MediaListItem(
                    $this->converter->toUuid($row->media_id),
                    $row->title,
                    $row->url,
                    $row->published_at->toDateTimeImmutable(),
                    MediaType::from($row->type),
                    MediaFormat::from($row->format),
                    $songs,
                );
            });

        $hasNextPage = $media->count() > $limit;
        $currentMedia = $hasNextPage ? $media->slice(0, $limit) : $media;
        $lastMedia = $hasNextPage ? $currentMedia->last() : null;

        $nextCursor = is_null($lastMedia)
            ? null
            : MediaListCursor::encode($lastMedia->publishedAt->format('Y-m-d'), $lastMedia->mediaId);

        return new MediaListPage($currentMedia->all(), $nextCursor);
    }
}
