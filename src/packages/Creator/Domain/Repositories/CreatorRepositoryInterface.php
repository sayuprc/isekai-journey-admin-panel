<?php

declare(strict_types=1);

namespace Creator\Domain\Repositories;

use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;

interface CreatorRepositoryInterface
{
    /**
     * @return array<Creator>
     */
    public function listCreators(): array;

    public function createCreator(Creator $creator): void;

    public function getCreator(CreatorId $creatorId): Creator;
}
