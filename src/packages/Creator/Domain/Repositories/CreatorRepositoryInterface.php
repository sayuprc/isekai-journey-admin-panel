<?php

declare(strict_types=1);

namespace Creator\Domain\Repositories;

use Creator\Domain\Models\Creator;

interface CreatorRepositoryInterface
{
    public function createCreator(Creator $creator): void;
}
