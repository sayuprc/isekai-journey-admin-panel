<?php

declare(strict_types=1);

namespace JourneyLog\Domain\Repositories;

use JourneyLog\Domain\Entities\JourneyLog;
use JourneyLog\Domain\Entities\JourneyLogId;

interface JourneyLogRepositoryInterface
{
    /**
     * @return JourneyLog[]
     */
    public function listJourneyLogs(): array;

    public function createJourneyLog(JourneyLog $journeyLog): void;

    public function getJourneyLog(JourneyLogId $journeyLogId): JourneyLog;

    public function editJourneyLog(JourneyLog $journeyLog): JourneyLogId;

    public function deleteJourneyLog(JourneyLogId $journeyLogId): void;
}
