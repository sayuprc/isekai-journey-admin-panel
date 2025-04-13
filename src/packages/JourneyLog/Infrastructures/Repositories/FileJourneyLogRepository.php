<?php

declare(strict_types=1);

namespace JourneyLog\Infrastructures\Repositories;

use JourneyLog\Domain\Models\JourneyLog;
use JourneyLog\Domain\Models\JourneyLogId;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use Support\Config\ConfigInterface;
use Support\Repository\FileStore;

class FileJourneyLogRepository implements JourneyLogRepositoryInterface
{
    private const string FILE_NAME = 'journey-logs.dat';

    private readonly string $filePath;

    /**
     * @param FileStore<JourneyLog> $store
     */
    public function __construct(
        private readonly FileStore $store,
        private readonly ConfigInterface $config,
    ) {
        $this->filePath = $this->config->getString('debug.file.path') . '/' . self::FILE_NAME;
    }

    /**
     * @return array<JourneyLog>
     */
    public function listJourneyLogs(): array
    {
        return array_values($this->store->getAll($this->filePath));
    }

    public function createJourneyLog(JourneyLog $journeyLog): void
    {
        $this->store->put($this->filePath, $journeyLog->journeyLogId->value, $journeyLog);
    }

    public function getJourneyLog(JourneyLogId $journeyLogId): JourneyLog
    {
        $found = $this->store->get($this->filePath, $journeyLogId->value);
        assert($found instanceof JourneyLog);

        return $found;
    }

    public function editJourneyLog(JourneyLog $journeyLog): JourneyLogId
    {
        $this->store->put($this->filePath, $journeyLog->journeyLogId->value, $journeyLog);

        return $journeyLog->journeyLogId;
    }

    public function deleteJourneyLog(JourneyLogId $journeyLogId): void
    {
        $this->store->unset($this->filePath, $journeyLogId->value);
    }
}
