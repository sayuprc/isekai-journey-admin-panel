<?php

declare(strict_types=1);

namespace Performer\DebugInfrastructures;

use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Support\DebugInfrastructures\Repository\DebugConfig;
use Support\DebugInfrastructures\Repository\FileStore;

readonly class FilePerformerRepository implements PerformerRepositoryInterface
{
    private const string FILE_NAME = 'performers.json';

    private string $filePath;

    /**
     * @param FileStore<Performer> $store
     */
    public function __construct(
        private FileStore $store,
        DebugConfig $config,
    ) {
        $this->filePath = $config->path . '/' . self::FILE_NAME;
    }

    public function all(): array
    {
        return array_values($this->store->getAll($this->filePath, Performer::class));
    }

    public function find(PerformerId $performerId): ?Performer
    {
        return $this->store->get($this->filePath, $performerId->value, Performer::class);
    }

    public function findByName(PerformerName $performerName): ?Performer
    {
        foreach ($this->store->getAll($this->filePath, Performer::class) as $performer) {
            if ($performer->performerName->value === $performerName->value) {
                return $performer;
            }
        }

        return null;
    }

    public function save(Performer $performer): Performer
    {
        $this->store->put($this->filePath, $performer->performerId->value, $performer, Performer::class);

        return $performer;
    }

    public function delete(PerformerId $performerId): void
    {
        $this->store->unset($this->filePath, $performerId->value, Performer::class);
    }

    public function getMaxOrderNo(): int
    {
        $performers = $this->all();

        uasort($performers, fn (Performer $a, Performer $b): int => $b->orderNo->value <=> $a->orderNo->value);

        return array_first($performers)->orderNo->value ?? 0;
    }
}
