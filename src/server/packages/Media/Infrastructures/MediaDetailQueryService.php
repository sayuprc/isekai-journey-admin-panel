<?php

declare(strict_types=1);

namespace Media\Infrastructures;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Media\Application\Query\MediaDetailQueryServiceInterface;
use Media\Application\Query\MediaReferencedSong;
use Media\Domain\Models\MediaId;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class MediaDetailQueryService implements MediaDetailQueryServiceInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function findReferencedSongs(MediaId $mediaId): array
    {
        /** @var Collection<int, object{song_id: string, title: string, song_order_no: int, media_order_no: int}> $rows */
        $rows = DB::table('song_media_links')
            ->join('songs', 'song_media_links.song_id', '=', 'songs.song_id')
            ->where('media_id', $this->converter->toBin($mediaId->value))
            ->orderBy('songs.order_no')
            ->orderBy('song_media_links.order_no')
            ->get([
                'song_media_links.song_id',
                'songs.title',
                'songs.order_no as song_order_no',
                'song_media_links.order_no as media_order_no',
            ]);

        return array_values(
            $rows
                ->map(fn (object $row): MediaReferencedSong => new MediaReferencedSong(
                    $this->converter->toUuid($row->song_id),
                    $row->title,
                    $row->song_order_no,
                    $row->media_order_no,
                ))
                ->all(),
        );
    }
}
