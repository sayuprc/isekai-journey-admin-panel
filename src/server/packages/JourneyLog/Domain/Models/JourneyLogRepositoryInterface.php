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

    public function save(JourneyLog $journeyLog): JourneyLog;

    public function delete(JourneyLogId $journeyLogId): void;
}
