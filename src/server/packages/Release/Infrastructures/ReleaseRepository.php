<?php

declare(strict_types=1);

namespace Release\Infrastructures;

use App\Models\Release\Release as ModelsRelease;
use App\Models\Release\TrackEntry as ModelsTrackEntry;
use DateType\ImmutableDate;
use Illuminate\Database\Eloquent\Builder;
use Override;
use Release\Domain\Criteria\ReleaseSearchCriteria;
use Release\Domain\Models\Release;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\SqlHelper;

readonly class ReleaseRepository implements ReleaseRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function search(ReleaseSearchCriteria $criteria): array
    {
        $query = $this->buildSearchQuery($criteria);
        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        return array_values($query
            ->orderBy('released_on', 'desc')
            ->orderBy('title')
            ->limit($criteria->perPage->value)
            ->offset($offset)
            ->get()
            ->map($this->hydrate(...))
            ->all());
    }

    #[Override]
    public function maxPage(ReleaseSearchCriteria $criteria): int
    {
        $count = $this->buildSearchQuery($criteria)->count();

        return (int)ceil($count / $criteria->perPage->value);
    }

    #[Override]
    public function save(Release $release): Release
    {
        $releaseId = $this->converter->toBin($release->releaseId->value);
        $data = $release->toArray();

        ModelsTrackEntry::query()->where('release_id', $releaseId)->delete();

        ModelsRelease::query()->upsert(
            [
                'release_id' => $releaseId,
                'title' => $data['title'],
                'type' => $data['type'],
                'distribution_type' => $data['distribution_type'],
                'released_on' => $data['released_on'],
                'description' => $data['description'],
                'is_display' => $data['is_display'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            ['release_id'],
            [
                'title',
                'type',
                'distribution_type',
                'released_on',
                'description',
                'is_display',
                'updated_at',
            ],
        );

        $trackEntries = array_map(
            fn (array $row): array => [
                'release_id' => $releaseId,
                'song_id' => $this->converter->toBin($row['song_id']),
                'track_no' => $row['track_no'],
            ],
            $data['track_entries'],
        );

        if ($trackEntries !== []) {
            ModelsTrackEntry::query()->insert($trackEntries);
        }

        return $release;
    }

    /**
     * @return Builder<ModelsRelease>
     */
    private function buildSearchQuery(ReleaseSearchCriteria $criteria)
    {
        $query = ModelsRelease::query()->with('trackEntries');

        if ($criteria->title->isPresent()) {
            $keyword = SqlHelper::escapeLike($criteria->title->get());
            $query = $query->whereLike('title', '%' . $keyword . '%');
        }

        if ($criteria->type->isPresent()) {
            $query = $query->where('type', $criteria->type->get()->value);
        }

        if ($criteria->distributionType->isPresent()) {
            $query = $query->where('distribution_type', $criteria->distributionType->get()->value);
        }

        if ($criteria->isDisplay->isPresent()) {
            $query = $query->where('is_display', $criteria->isDisplay->get());
        }

        return $query;
    }

    private function hydrate(ModelsRelease $row): Release
    {
        /** @var list<array{songId: string, trackNo: int}> $trackEntries */
        $trackEntries = $row->trackEntries
            ->map(fn (ModelsTrackEntry $trackEntry): array => [
                'songId' => $this->converter->toUuid($trackEntry->song_id),
                'trackNo' => $trackEntry->track_no,
            ])
            ->values()
            ->all();

        return Release::reconstruct(
            $this->converter->toUuid($row->release_id),
            $row->title,
            $row->type,
            $row->distribution_type,
            ImmutableDate::createFromInterface($row->released_on),
            $row->description,
            $row->is_display,
            $trackEntries,
        );
    }
}
