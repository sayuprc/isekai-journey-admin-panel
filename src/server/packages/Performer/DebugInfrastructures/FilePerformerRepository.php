<?php

declare(strict_types=1);

namespace Performer\DebugInfrastructures;

use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerName;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Support\Contracts\ConfigInterface;
use Support\DebugInfrastructures\Repository\FileStore;

readonly class FilePerformerRepository implements PerformerRepositoryInterface
{
    private const string FILE_NAME = 'performers.dat';

    private string $filePath;

    /**
     * @param FileStore<Performer> $store
     */
    public function __construct(
        private FileStore $store,
        private ConfigInterface $config,
    ) {
        $this->filePath = $this->config->getString('debug.file.path') . '/' . self::FILE_NAME;
    }

    public function all(): array
    {
        return array_values($this->store->getAll($this->filePath));
    }

    public function findByName(PerformerName $performerName): ?Performer
    {
        foreach ($this->store->getAll($this->filePath) as $performer) {
            if ($performer->performerName->value === $performerName->value) {
                return $performer;
            }
        }

        return null;
    }

    public function insert(Performer $performer): void
    {
        $this->store->put($this->filePath, $performer->performerId->value, $performer);
    }
}
