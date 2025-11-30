<?php

declare(strict_types=1);

namespace Performer\Domain\Services;

use Performer\Domain\Models\PerformerId;
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

    public function existsForUpdate(PerformerId $targetPerformerId, PerformerName $updatedPerformerName): bool
    {
        $found = $this->repository->findByName($updatedPerformerName);

        if (is_null($found)) {
            return false;
        }

        return $found->performerId->value !== $targetPerformerId->value;
    }
}
