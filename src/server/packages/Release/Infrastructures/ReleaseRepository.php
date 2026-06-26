<?php

declare(strict_types=1);

namespace Release\Infrastructures;

use DateTimeImmutable;
use DateType\ImmutableDate;
use Emonkak\Orm\SelectBuilder;
use Override;
use Release\Domain\Criteria\ReleaseSearchCriteria;
use Release\Domain\Models\Release;
use Release\Domain\Models\ReleaseId;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Support\Infrastructures\Database\SqlHelper;

readonly class ReleaseRepository implements ReleaseRepositoryInterface
{
    private const string TABLE = 'releases';

    private const string TRACK_TABLE = 'release_track_entries';

    /** @var list<string> */
    private const array COLUMNS = ['release_id', 'title', 'type', 'distribution_type', 'released_on', 'description', 'is_display'];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function search(ReleaseSearchCriteria $criteria): array
    {
        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        $releaseRows = $this->queryFactory->fetchAll(
            $this->buildSearchQuery($criteria)
                ->withSelect(self::COLUMNS)
                ->orderBy('released_on', 'desc')
                ->orderBy('title')
                ->limit($criteria->perPage->value)
                ->offset($offset),
        );

        $tracksByRelease = $this->loadTrackEntries(
            array_map(fn (array $row): string => Row::string($row, 'release_id'), $releaseRows),
        );

        return array_map(
            fn (array $releaseRow): Release => $this->hydrate(
                $releaseRow,
                $tracksByRelease[Row::string($releaseRow, 'release_id')] ?? [],
            ),
            $releaseRows,
        );
    }

    #[Override]
    public function find(ReleaseId $releaseId): ?Release
    {
        $binReleaseId = $this->converter->toBin($releaseId->value);

        $releaseRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('release_id', '=', $binReleaseId)
                ->limit(1),
        );

        $releaseRow = $releaseRows[0] ?? null;

        if (is_null($releaseRow)) {
            return null;
        }

        $tracksByRelease = $this->loadTrackEntries([$binReleaseId]);

        return $this->hydrate($releaseRow, $tracksByRelease[$binReleaseId] ?? []);
    }

    #[Override]
    public function maxPage(ReleaseSearchCriteria $criteria): int
    {
        $count = Row::intValue($this->buildSearchQuery($criteria)->aggregate($this->queryFactory->pdo(), 'COUNT(*)'));

        return (int)ceil($count / $criteria->perPage->value);
    }

    #[Override]
    public function save(Release $release): Release
    {
        $binReleaseId = $this->converter->toBin($release->releaseId->value);
        $data = $release->toArray();
        $now = now()->toDateTimeString();

        // トラックは洗い替えする。
        $this->queryFactory->delete()
            ->from(self::TRACK_TABLE)
            ->where('release_id', '=', $binReleaseId)
            ->execute($this->queryFactory->pdo());

        $this->queryFactory->insert()
            ->into(self::TABLE, ['release_id', 'title', 'type', 'distribution_type', 'released_on', 'description', 'is_display', 'created_at', 'updated_at'])
            ->values([
                $binReleaseId,
                $data['title'],
                $data['type'],
                $data['distribution_type'],
                $data['released_on'],
                $data['description'],
                $data['is_display'],
                $now,
                $now,
            ])
            ->build()
            ->append(
                'ON DUPLICATE KEY UPDATE '
                . '`title` = VALUES(`title`), '
                . '`type` = VALUES(`type`), '
                . '`distribution_type` = VALUES(`distribution_type`), '
                . '`released_on` = VALUES(`released_on`), '
                . '`description` = VALUES(`description`), '
                . '`is_display` = VALUES(`is_display`), '
                . '`updated_at` = VALUES(`updated_at`)',
            )
            ->execute($this->queryFactory->pdo());

        $trackEntries = array_map(
            fn (array $row): array => [
                $binReleaseId,
                $this->converter->toBin($row['song_id']),
                $row['track_no'],
            ],
            $data['track_entries'],
        );

        if ($trackEntries !== []) {
            $this->queryFactory->insert()
                ->into(self::TRACK_TABLE, ['release_id', 'song_id', 'track_no'])
                ->values(...$trackEntries)
                ->execute($this->queryFactory->pdo());
        }

        return $release;
    }

    #[Override]
    public function delete(ReleaseId $releaseId): void
    {
        $this->queryFactory->delete()
            ->from(self::TABLE)
            ->where('release_id', '=', $this->converter->toBin($releaseId->value))
            ->execute($this->queryFactory->pdo());
    }

    private function buildSearchQuery(ReleaseSearchCriteria $criteria): SelectBuilder
    {
        $query = $this->queryFactory->select()->from(self::TABLE);

        if ($criteria->title->isPresent()) {
            $keyword = SqlHelper::escapeLike($criteria->title->get());
            $query = $query->where('title', 'LIKE', '%' . $keyword . '%');
        }

        if ($criteria->type->isPresent()) {
            $query = $query->where('type', '=', $criteria->type->get()->value);
        }

        if ($criteria->distributionType->isPresent()) {
            $query = $query->where('distribution_type', '=', $criteria->distributionType->get()->value);
        }

        if ($criteria->isDisplay->isPresent()) {
            $query = $query->where('is_display', '=', $criteria->isDisplay->get());
        }

        return $query;
    }

    /**
     * @param list<string> $binReleaseIds
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function loadTrackEntries(array $binReleaseIds): array
    {
        if ($binReleaseIds === []) {
            return [];
        }

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['release_id', 'song_id', 'track_no'])
                ->from(self::TRACK_TABLE)
                ->where('release_id', 'IN', $binReleaseIds)
                ->orderBy('track_no'),
        );

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[Row::string($row, 'release_id')][] = $row;
        }

        return $grouped;
    }

    /**
     * @param array<string, mixed>       $releaseRow
     * @param list<array<string, mixed>> $trackRows
     */
    private function hydrate(array $releaseRow, array $trackRows): Release
    {
        $trackEntries = array_map(
            fn (array $trackRow): array => [
                'songId' => $this->converter->toUuid(Row::string($trackRow, 'song_id')),
                'trackNo' => Row::int($trackRow, 'track_no'),
            ],
            $trackRows,
        );

        return Release::reconstruct(
            $this->converter->toUuid(Row::string($releaseRow, 'release_id')),
            Row::string($releaseRow, 'title'),
            Row::int($releaseRow, 'type'),
            Row::int($releaseRow, 'distribution_type'),
            ImmutableDate::createFromInterface(new DateTimeImmutable(Row::string($releaseRow, 'released_on'))),
            Row::string($releaseRow, 'description'),
            Row::bool($releaseRow, 'is_display'),
            $trackEntries,
        );
    }
}
