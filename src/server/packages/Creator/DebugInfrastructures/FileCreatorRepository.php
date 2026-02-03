<?php

declare(strict_types=1);

namespace Creator\DebugInfrastructures;

use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Support\DebugInfrastructures\Repository\DebugConfig;
use Support\DebugInfrastructures\Repository\FileStore;

readonly class FileCreatorRepository implements CreatorRepositoryInterface
{
    private const string FILE_NAME = 'creators.dat';

    private string $filePath;

    /**
     * @param FileStore<Creator> $store
     */
    public function __construct(
        private FileStore $store,
        DebugConfig $config,
    ) {
        $this->filePath = $config->path . '/' . self::FILE_NAME;
    }

    /**
     * @return array<Creator>
     */
    public function all(): array
    {
        return array_values($this->store->getAll($this->filePath));
    }

    public function find(CreatorId $creatorId): ?Creator
    {
        return $this->store->get($this->filePath, $creatorId->value);
    }

    public function findByName(CreatorName $creatorName): ?Creator
    {
        foreach ($this->store->getAll($this->filePath) as $creator) {
            if ($creator->creatorName->value === $creatorName->value) {
                return $creator;
            }
        }

        return null;
    }

    public function save(Creator $creator): Creator
    {
        $this->store->put($this->filePath, $creator->creatorId->value, $creator);

        return $creator;
    }

    public function delete(CreatorId $creatorId): void
    {
        $this->store->unset($this->filePath, $creatorId->value);
    }
}
