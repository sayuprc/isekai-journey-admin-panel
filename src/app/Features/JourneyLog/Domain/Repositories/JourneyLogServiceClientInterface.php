<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Domain\Repositories;

use App\Features\JourneyLog\Domain\Entities\JourneyLog;
use App\Features\JourneyLog\Domain\Entities\JourneyLogId;

interface JourneyLogServiceClientInterface
{
    /**
     * @return array<JourneyLog>
     */
    public function listJourneyLogs(): array;

    public function createJourneyLog(JourneyLog $journeyLog): void;

    public function getJourneyLog(JourneyLogId $journeyLogId): JourneyLog;

    public function editJourneyLog(JourneyLog $journeyLog): JourneyLogId;

    public function deleteJourneyLog(JourneyLogId $journeyLogId): void;
}
