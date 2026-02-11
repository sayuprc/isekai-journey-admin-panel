<?php

declare(strict_types=1);

namespace Performer\Domain\Models;

interface PerformerRepositoryInterface
{
    /**
     * @return array<Performer>
     */
    public function all(): array;

    public function find(PerformerId $performerId): ?Performer;

    public function findByName(PerformerName $performerName): ?Performer;

    public function save(Performer $performer): Performer;

    public function delete(PerformerId $performerId): void;

    public function getMaxOrderNo(): int;
}
