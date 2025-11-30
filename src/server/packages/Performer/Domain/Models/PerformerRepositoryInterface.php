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

    public function insert(Performer $performer): void;

    public function update(Performer $performer): PerformerId;
}
