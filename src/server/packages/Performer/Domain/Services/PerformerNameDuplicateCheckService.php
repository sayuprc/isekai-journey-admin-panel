<?php

declare(strict_types=1);

namespace Performer\Domain\Services;

use Performer\Domain\Models\PerformerName;
use Performer\Domain\Models\PerformerRepositoryInterface;

class PerformerNameDuplicateCheckService
{
    public function __construct(private readonly PerformerRepositoryInterface $repository)
    {
    }

    public function exists(PerformerName $performerName): bool
    {
        return ! is_null($this->repository->findByName($performerName));
    }
}
