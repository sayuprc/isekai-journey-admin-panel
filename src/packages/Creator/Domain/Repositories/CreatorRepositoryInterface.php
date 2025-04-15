<?php

declare(strict_types=1);

namespace Creator\Domain\Repositories;

use Creator\Domain\Models\Creator;

interface CreatorRepositoryInterface
{
    /**
     * @return array<Creator>
     */
    public function listCreators(): array;

    public function createCreator(Creator $creator): void;
}
