<?php

declare(strict_types=1);

namespace JourneyLog\Domain\Models;

interface JourneyLogRepositoryInterface
{
    /**
     * @return array<JourneyLog>
     */
    public function all(): array;

    public function find(JourneyLogId $journeyLogId): ?JourneyLog;

    public function insert(JourneyLog $journeyLog): void;

    public function update(JourneyLog $journeyLog): JourneyLogId;

    public function delete(JourneyLogId $journeyLogId): void;
}
