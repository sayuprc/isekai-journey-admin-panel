<?php

declare(strict_types=1);

namespace Performer\Domain\Models;

interface PerformerRepositoryInterface
{
    /**
     * @return array<Performer>
     */
    public function all(): array;

    public function findByName(PerformerName $performerName): ?Performer;

    public function insert(Performer $performer): void;
}
