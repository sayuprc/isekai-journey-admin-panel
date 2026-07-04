<?php

declare(strict_types=1);

namespace Release\Infrastructures\Admin;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Override;
use Release\Application\Admin\Query\ReleaseDetailQueryServiceInterface;
use Release\Application\Admin\Query\ReleaseReferencedSong;
use Release\Domain\Models\ReleaseId;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class ReleaseDetailQueryService implements ReleaseDetailQueryServiceInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function findReferencedSongs(ReleaseId $releaseId): array
    {
        /** @var Collection<int, object{position: int, track_no: int, song_id: string, title: string}> $rows */
        $rows = DB::table('release_tracks')
            ->join('songs', 'release_tracks.song_id', '=', 'songs.song_id')
            ->where('release_tracks.release_id', $this->converter->toBin($releaseId->value))
            ->orderBy('release_tracks.position')
            ->orderBy('release_tracks.track_no')
            ->get([
                'release_tracks.position',
                'release_tracks.track_no',
                'release_tracks.song_id',
                'songs.title',
            ]);

        return array_values(
            $rows
                ->map(fn (object $row): ReleaseReferencedSong => new ReleaseReferencedSong(
                    $row->position,
                    $row->track_no,
                    $this->converter->toUuid($row->song_id),
                    $row->title,
                ))
                ->all(),
        );
    }
}
