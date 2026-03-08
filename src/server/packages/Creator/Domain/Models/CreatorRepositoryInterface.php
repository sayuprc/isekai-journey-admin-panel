<?php

declare(strict_types=1);

namespace Creator\Domain\Models;

use Creator\Domain\Criteria\CreatorSearchCriteria;

interface CreatorRepositoryInterface
{
    /**
     * @return array<Creator>
     */
    public function all(): array;

    /**
     * @return array<Creator>
     */
    public function search(CreatorSearchCriteria $criteria): array;

    public function maxPage(CreatorSearchCriteria $criteria): int;

    public function find(CreatorId $creatorId): ?Creator;

    public function findByName(CreatorName $name): ?Creator;

    /**
     * @return array<Creator>
     */
    public function findByIds(CreatorId ...$creatorIds): array;

    public function save(Creator $creator): Creator;

    public function delete(CreatorId $creatorId): void;

    public function getMaxOrderNo(): int;
}
