<?php

declare(strict_types=1);

namespace Creator\Domain\Services;

use Creator\Domain\Models\CreatorName;
use Creator\Domain\Repositories\CreatorRepositoryInterface;

class CreatorNameDuplicateCheckService
{
    public function __construct(private readonly CreatorRepositoryInterface $repository)
    {
    }

    public function exists(CreatorName $creatorName): bool
    {
        return ! is_null($this->repository->findByName($creatorName));
    }
}
