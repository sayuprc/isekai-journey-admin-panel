<?php

declare(strict_types=1);

namespace Place\Domain\Models;

use Place\Domain\Criteria\PlaceSearchCriteria;

interface PlaceRepositoryInterface
{
    /**
     * @return array<Place>
     */
    public function all(): array;

    /**
     * @return array<Place>
     */
    public function search(PlaceSearchCriteria $criteria): array;

    public function maxPage(PlaceSearchCriteria $criteria): int;

    public function find(PlaceId $placeId): ?Place;

    /**
     * @return array<Place>
     */
    public function findByIds(PlaceId ...$placeIds): array;

    public function findByName(PlaceName $name): ?Place;

    public function save(Place $place): Place;

    public function delete(PlaceId $placeId): void;
}
