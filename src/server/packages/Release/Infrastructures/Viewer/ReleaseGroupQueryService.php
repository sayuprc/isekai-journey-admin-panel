<?php

declare(strict_types=1);

namespace Release\Infrastructures\Viewer;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Override;
use Release\Application\Viewer\Query\ReleaseGroupListCursor;
use Release\Application\Viewer\Query\ReleaseGroupListItem;
use Release\Application\Viewer\Query\ReleaseGroupListPage;
use Release\Application\Viewer\Query\ReleaseGroupQueryServiceInterface;
use Release\Application\Viewer\Query\ReleaseListItem;
use Release\Application\Viewer\Query\ReleaseMediumItem;
use Release\Application\Viewer\Query\ReleaseTrackItem;
use Release\Domain\Models\MediumFormat;
use Release\Domain\Models\ReleaseGroupType;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class ReleaseGroupQueryService implements ReleaseGroupQueryServiceInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function list(?string $cursor, int $limit): ReleaseGroupListPage
    {
        // 公開リリースを 1 件以上持つ公開グループのみを対象にする。
        $query = DB::table('release_groups')
            ->join('releases', function (JoinClause $join): void {
                $join->on('releases.release_group_id', '=', 'release_groups.release_group_id')
                    ->where('releases.is_display', '=', true);
            })
            ->where('release_groups.is_display', true)
            ->groupBy(
                'release_groups.release_group_id',
                'release_groups.title',
                'release_groups.type',
                'release_groups.description',
            );

        if (is_string($cursor)) {
            $decoded = ReleaseGroupListCursor::decode($cursor);

            // キーセットページング: (first_released_on DESC, release_group_id ASC) で cursor より後ろを取る
            $query = $query->havingRaw(
                '(MIN(releases.released_on) < ? OR (MIN(releases.released_on) = ? AND release_groups.release_group_id > ?))',
                [
                    $decoded->firstReleasedOn,
                    $decoded->firstReleasedOn,
                    $this->converter->toBin($decoded->releaseGroupId),
                ],
            );
        }

        /** @var Collection<int, object{release_group_id: string, title: string, type: int, description: string, first_released_on: string}> $groupRows */
        $groupRows = $query
            ->orderByDesc('first_released_on')
            ->orderBy('release_groups.release_group_id')
            ->limit($limit + 1)
            ->get([
                'release_groups.release_group_id',
                'release_groups.title',
                'release_groups.type',
                'release_groups.description',
                DB::raw('MIN(releases.released_on) as first_released_on'),
            ]);

        $hasNextPage = $groupRows->count() > $limit;
        $pageRows = $hasNextPage ? $groupRows->slice(0, $limit)->values() : $groupRows;

        /** @var list<string> $binGroupIds */
        $binGroupIds = $pageRows->pluck('release_group_id')->all();
        $releasesByGroup = $this->loadReleases($binGroupIds);

        $releaseGroups = $pageRows
            ->map(fn (object $row): ReleaseGroupListItem => new ReleaseGroupListItem(
                $this->converter->toUuid($row->release_group_id),
                $row->title,
                ReleaseGroupType::from($row->type),
                $row->description,
                $row->first_released_on,
                $releasesByGroup[$row->release_group_id] ?? [],
            ))
            ->all();

        $lastRow = $hasNextPage && $pageRows->isNotEmpty() ? $pageRows->last() : null;

        $nextCursor = is_null($lastRow)
            ? null
            : ReleaseGroupListCursor::encode(
                $lastRow->first_released_on,
                $this->converter->toUuid($lastRow->release_group_id),
            );

        return new ReleaseGroupListPage(array_values($releaseGroups), $nextCursor);
    }

    /**
     * @param list<string> $binGroupIds
     *
     * @return array<string, list<ReleaseListItem>>
     */
    private function loadReleases(array $binGroupIds): array
    {
        if ($binGroupIds === []) {
            return [];
        }

        /** @var Collection<int, object{release_id: string, release_group_id: string, name: string, released_on: string, description: string, jacket_art_url: string|null}> $releaseRows */
        $releaseRows = DB::table('releases')
            ->whereIn('release_group_id', $binGroupIds)
            ->where('is_display', true)
            ->orderBy('released_on')
            ->orderBy('name')
            ->get(['release_id', 'release_group_id', 'name', 'released_on', 'description', 'jacket_art_url']);

        /** @var list<string> $binReleaseIds */
        $binReleaseIds = $releaseRows->pluck('release_id')->all();
        $mediaByRelease = $this->loadMedia($binReleaseIds);

        $grouped = [];

        foreach ($releaseRows as $row) {
            $grouped[$row->release_group_id][] = new ReleaseListItem(
                $this->converter->toUuid($row->release_id),
                $row->name,
                $row->released_on,
                $row->description,
                $row->jacket_art_url,
                $mediaByRelease[$row->release_id] ?? [],
            );
        }

        return $grouped;
    }

    /**
     * @param list<string> $binReleaseIds
     *
     * @return array<string, list<ReleaseMediumItem>>
     */
    private function loadMedia(array $binReleaseIds): array
    {
        if ($binReleaseIds === []) {
            return [];
        }

        /** @var Collection<int, object{release_id: string, position: int, format: int}> $mediumRows */
        $mediumRows = DB::table('release_media')
            ->whereIn('release_id', $binReleaseIds)
            ->orderBy('position')
            ->get(['release_id', 'position', 'format']);

        // 収録曲は公開楽曲のみを載せる。
        /** @var Collection<int, object{release_id: string, position: int, track_no: int, song_id: string, title: string}> $trackRows */
        $trackRows = DB::table('release_tracks')
            ->join('songs', function (JoinClause $join): void {
                $join->on('songs.song_id', '=', 'release_tracks.song_id')
                    ->where('songs.is_display', '=', true);
            })
            ->whereIn('release_tracks.release_id', $binReleaseIds)
            ->orderBy('release_tracks.position')
            ->orderBy('release_tracks.track_no')
            ->get([
                'release_tracks.release_id',
                'release_tracks.position',
                'release_tracks.track_no',
                'release_tracks.song_id',
                'songs.title',
            ]);

        $tracksByMedium = [];

        foreach ($trackRows as $row) {
            $tracksByMedium[$row->release_id][$row->position][] = new ReleaseTrackItem(
                $row->track_no,
                $this->converter->toUuid($row->song_id),
                $row->title,
            );
        }

        $grouped = [];

        foreach ($mediumRows as $row) {
            $grouped[$row->release_id][] = new ReleaseMediumItem(
                $row->position,
                MediumFormat::from($row->format),
                $tracksByMedium[$row->release_id][$row->position] ?? [],
            );
        }

        return $grouped;
    }
}
