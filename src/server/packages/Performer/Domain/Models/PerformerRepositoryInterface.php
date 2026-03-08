<?php

declare(strict_types=1);

namespace Performer\Domain\Models;

use Performer\Domain\Criteria\PerformerSearchCriteria;

interface PerformerRepositoryInterface
{
    /**
     * @return array<Performer>
     */
    public function all(): array;

    /**
     * @return array<Performer>
     */
    public function search(PerformerSearchCriteria $criteria): array;

    public function maxPage(PerformerSearchCriteria $criteria): int;

    public function find(PerformerId $performerId): ?Performer;

    public function findByName(PerformerName $name): ?Performer;

    public function save(Performer $performer): Performer;

    public function delete(PerformerId $performerId): void;

    public function getMaxOrderNo(): int;
}
