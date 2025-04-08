<?php

declare(strict_types=1);

namespace JourneyLog\Infrastructures\Repositories;

use JourneyLog\Domain\Entities\JourneyLog;
use JourneyLog\Domain\Entities\JourneyLogId;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use Support\Repository\FileStore;

class FileJourneyLogRepository implements JourneyLogRepositoryInterface
{
    private const string PATH = 'journey-logs.dat';

    /**
     * @param FileStore<JourneyLog> $store
     */
    public function __construct(private readonly FileStore $store)
    {
    }

    /**
     * @return array<JourneyLog>
     */
    public function listJourneyLogs(): array
    {
        return array_values($this->store->getAll(self::PATH));
    }

    public function createJourneyLog(JourneyLog $journeyLog): void
    {
        $this->store->put(self::PATH, $journeyLog->journeyLogId->value, $journeyLog);
    }

    public function getJourneyLog(JourneyLogId $journeyLogId): JourneyLog
    {
        $found = $this->store->get(self::PATH, $journeyLogId->value);
        assert($found instanceof JourneyLog);

        return $found;
    }

    public function editJourneyLog(JourneyLog $journeyLog): JourneyLogId
    {
        $this->store->put(self::PATH, $journeyLog->journeyLogId->value, $journeyLog);

        return $journeyLog->journeyLogId;
    }

    public function deleteJourneyLog(JourneyLogId $journeyLogId): void
    {
        $this->store->unset(self::PATH, $journeyLogId->value);
    }
}
