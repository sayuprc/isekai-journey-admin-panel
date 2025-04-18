<?php

declare(strict_types=1);

namespace JourneyLog\Domain\Repositories;

use JourneyLog\Domain\Models\JourneyLog;
use JourneyLog\Domain\Models\JourneyLogId;

interface JourneyLogRepositoryInterface
{
    /**
     * @return array<JourneyLog>
     */
    public function all(): array;

    public function find(JourneyLogId $journeyLogId): JourneyLog;

    public function insert(JourneyLog $journeyLog): void;

    public function update(JourneyLog $journeyLog): JourneyLogId;

    public function delete(JourneyLogId $journeyLogId): void;
}
