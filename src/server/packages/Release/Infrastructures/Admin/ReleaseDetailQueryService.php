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
        /** @var Collection<int, object{song_id: string, title: string, track_no: int}> $rows */
        $rows = DB::table('release_track_entries')
            ->join('songs', 'release_track_entries.song_id', '=', 'songs.song_id')
            ->where('release_track_entries.release_id', $this->converter->toBin($releaseId->value))
            ->orderBy('release_track_entries.track_no')
            ->get([
                'release_track_entries.song_id',
                'songs.title',
                'release_track_entries.track_no',
            ]);

        return array_values(
            $rows
                ->map(fn (object $row): ReleaseReferencedSong => new ReleaseReferencedSong(
                    $this->converter->toUuid($row->song_id),
                    $row->title,
                    $row->track_no,
                ))
                ->all(),
        );
    }
}
