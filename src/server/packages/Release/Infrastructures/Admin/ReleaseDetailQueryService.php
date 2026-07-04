<?php

declare(strict_types=1);

namespace Release\Infrastructures\Admin;

use Override;
use Release\Application\Admin\Query\ReleaseDetailQueryServiceInterface;
use Release\Application\Admin\Query\ReleaseReferencedSong;
use Release\Domain\Models\ReleaseId;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class ReleaseDetailQueryService implements ReleaseDetailQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function findReferencedSongs(ReleaseId $releaseId): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect([
                    'release_tracks.position',
                    'release_tracks.track_no',
                    'release_tracks.song_id',
                    'songs.title',
                ])
                ->from('release_tracks')
                ->join('songs', 'release_tracks.song_id = songs.song_id')
                ->where('release_tracks.release_id', '=', $this->converter->toBin($releaseId->value))
                ->orderBy('release_tracks.position')
                ->orderBy('release_tracks.track_no'),
        );

        return array_map(
            fn (array $row): ReleaseReferencedSong => new ReleaseReferencedSong(
                Row::int($row, 'position'),
                Row::int($row, 'track_no'),
                $this->converter->toUuid(Row::string($row, 'song_id')),
                Row::string($row, 'title'),
            ),
            $rows,
        );
    }
}
