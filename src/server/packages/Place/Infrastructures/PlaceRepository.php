<?php

declare(strict_types=1);

namespace Place\Infrastructures;

use Emonkak\Orm\SelectBuilder;
use Override;
use Place\Domain\Criteria\PlaceSearchCriteria;
use Place\Domain\Models\Place;
use Place\Domain\Models\PlaceId;
use Place\Domain\Models\PlaceName;
use Place\Domain\Models\PlaceRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Support\Infrastructures\Database\SqlHelper;

readonly class PlaceRepository implements PlaceRepositoryInterface
{
    private const string TABLE = 'places';

    /** @var list<string> */
    private const array COLUMNS = ['place_id', 'name', 'kind'];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function all(): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->orderBy('name'),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function search(PlaceSearchCriteria $criteria): array
    {
        $query = $this->applyFilters(
            $this->queryFactory->select()->withSelect(self::COLUMNS)->from(self::TABLE),
            $criteria,
        );

        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        $rows = $this->queryFactory->fetchAll(
            $query
                ->orderBy($criteria->sort->value, $criteria->order->value)
                ->limit($criteria->perPage->value)
                ->offset($offset),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function maxPage(PlaceSearchCriteria $criteria): int
    {
        $query = $this->applyFilters(
            $this->queryFactory->select()->from(self::TABLE),
            $criteria,
        );

        $count = Row::intValue($query->aggregate($this->queryFactory->pdo(), 'COUNT(*)'));

        return (int)ceil($count / $criteria->perPage->value);
    }

    #[Override]
    public function find(PlaceId $placeId): ?Place
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('place_id', '=', $this->converter->toBin($placeId->value))
                ->limit(1),
        );

        $row = $rows[0] ?? null;

        return is_null($row) ? null : $this->hydrate($row);
    }

    #[Override]
    public function findByIds(PlaceId ...$placeIds): array
    {
        if ($placeIds === []) {
            return [];
        }

        $binIds = array_map(fn (PlaceId $placeId): string => $this->converter->toBin($placeId->value), $placeIds);

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('place_id', 'IN', $binIds),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function findByName(PlaceName $name): ?Place
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('name', '=', $name->value)
                ->limit(1),
        );

        $row = $rows[0] ?? null;

        return is_null($row) ? null : $this->hydrate($row);
    }

    #[Override]
    public function save(Place $place): Place
    {
        $now = now()->toDateTimeString();

        // emonkak のビルダは upsert を直接表現できないため、INSERT に
        // ON DUPLICATE KEY UPDATE を付与する。VALUES(col) で挿入値を再利用し追加バインドを避ける
        $this->queryFactory->insert()
            ->into(self::TABLE, ['place_id', 'name', 'kind', 'created_at', 'updated_at'])
            ->values([
                $this->converter->toBin($place->placeId->value),
                $place->name->value,
                $place->kind->value,
                $now,
                $now,
            ])
            ->build()
            ->append('ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `kind` = VALUES(`kind`), `updated_at` = VALUES(`updated_at`)')
            ->execute($this->queryFactory->pdo());

        return $place;
    }

    #[Override]
    public function delete(PlaceId $placeId): void
    {
        $this->queryFactory->delete()
            ->from(self::TABLE)
            ->where('place_id', '=', $this->converter->toBin($placeId->value))
            ->execute($this->queryFactory->pdo());
    }

    private function applyFilters(SelectBuilder $query, PlaceSearchCriteria $criteria): SelectBuilder
    {
        if ($criteria->name->isPresent()) {
            $query = $query->where(
                'name_lower',
                'LIKE',
                '%' . SqlHelper::escapeLike(mb_strtolower($criteria->name->get())) . '%',
            );
        }

        if ($criteria->kind->isPresent()) {
            $query = $query->where('kind', '=', $criteria->kind->get()->value);
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Place
    {
        return Place::reconstruct(
            $this->converter->toUuid(Row::string($row, 'place_id')),
            Row::string($row, 'name'),
            Row::int($row, 'kind'),
        );
    }
}
