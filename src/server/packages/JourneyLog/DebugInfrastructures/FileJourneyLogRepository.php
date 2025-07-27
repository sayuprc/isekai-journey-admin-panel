<?php

declare(strict_types=1);

namespace JourneyLog\DebugInfrastructures;

use JourneyLog\Domain\Models\JourneyLog;
use JourneyLog\Domain\Models\JourneyLogId;
use JourneyLog\Domain\Models\JourneyLogRepositoryInterface;
use Support\Contracts\ConfigInterface;
use Support\Repository\FileStore;

readonly class FileJourneyLogRepository implements JourneyLogRepositoryInterface
{
    private const string FILE_NAME = 'journey-logs.dat';

    private string $filePath;

    /**
     * @param FileStore<JourneyLog> $store
     */
    public function __construct(
        private FileStore $store,
        private ConfigInterface $config,
    ) {
        $this->filePath = $this->config->getString('debug.file.path') . '/' . self::FILE_NAME;
    }

    /**
     * @return array<JourneyLog>
     */
    public function all(): array
    {
        return array_values($this->store->getAll($this->filePath));
    }

    public function find(JourneyLogId $journeyLogId): ?JourneyLog
    {
        return $this->store->get($this->filePath, $journeyLogId->value);
    }

    public function insert(JourneyLog $journeyLog): void
    {
        $this->store->put($this->filePath, $journeyLog->journeyLogId->value, $journeyLog);
    }

    public function update(JourneyLog $journeyLog): JourneyLogId
    {
        $this->store->put($this->filePath, $journeyLog->journeyLogId->value, $journeyLog);

        return $journeyLog->journeyLogId;
    }

    public function delete(JourneyLogId $journeyLogId): void
    {
        $this->store->unset($this->filePath, $journeyLogId->value);
    }
}
