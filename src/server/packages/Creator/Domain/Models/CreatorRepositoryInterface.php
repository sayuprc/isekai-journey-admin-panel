<?php

declare(strict_types=1);

namespace Creator\Domain\Models;

interface CreatorRepositoryInterface
{
    /**
     * @return array<Creator>
     */
    public function all(): array;

    public function find(CreatorId $creatorId): ?Creator;

    public function findByName(CreatorName $creatorName): ?Creator;

    /**
     * @return array<Creator>
     */
    public function findByIds(CreatorId ...$creatorIds): array;

    public function save(Creator $creator): Creator;

    public function delete(CreatorId $creatorId): void;
}
