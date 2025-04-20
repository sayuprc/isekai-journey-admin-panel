<?php

declare(strict_types=1);

namespace Creator\Domain\Repositories;

use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;

interface CreatorRepositoryInterface
{
    /**
     * @return array<Creator>
     */
    public function all(): array;

    public function find(CreatorId $creatorId): ?Creator;

    public function findByName(CreatorName $creatorName): ?Creator;

    public function insert(Creator $creator): void;

    public function update(Creator $creator): CreatorId;

    public function delete(CreatorId $creatorId): void;
}
