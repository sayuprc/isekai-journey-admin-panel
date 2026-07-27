<?php

declare(strict_types=1);

namespace Release\Domain\Models;

interface ReleaseGroupRepositoryInterface
{
    public function find(ReleaseGroupId $releaseGroupId): ?ReleaseGroup;

    public function save(ReleaseGroup $releaseGroup): ReleaseGroup;

    public function delete(ReleaseGroupId $releaseGroupId): void;

    /**
     * @return list<array{id: string, order_no: int}>
     */
    public function resetOrderNumbers(): array;
}
