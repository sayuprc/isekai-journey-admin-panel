<?php

declare(strict_types=1);

namespace SongType\DebugInfrastructures;

use SongType\Domain\Models\SongType;
use SongType\Domain\Models\SongTypeId;
use SongType\Domain\Models\SongTypeName;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use Support\DebugInfrastructures\Repository\FileRepositoryConfig;
use Support\DebugInfrastructures\Repository\FileStore;

readonly class FileSongTypeRepository implements SongTypeRepositoryInterface
{
    private const string FILE_NAME = 'song-types.dat';

    private string $filePath;

    /**
     * @param FileStore<SongType> $store
     */
    public function __construct(
        private FileStore $store,
        FileRepositoryConfig $config,
    ) {
        $this->filePath = $config->filePath . '/' . self::FILE_NAME;
    }

    /**
     * @return array<SongType>
     */
    public function all(): array
    {
        return array_values($this->store->getAll($this->filePath));
    }

    public function find(SongTypeId $songTypeId): ?SongType
    {
        return $this->store->get($this->filePath, $songTypeId->value);
    }

    public function findByName(SongTypeName $songTypeName): ?SongType
    {
        foreach ($this->store->getAll($this->filePath) as $songType) {
            if ($songType->songTypeName->value === $songTypeName->value) {
                return $songType;
            }
        }

        return null;
    }

    public function insert(SongType $songType): void
    {
        $this->store->put($this->filePath, $songType->songTypeId->value, $songType);
    }

    public function update(SongType $songType): SongTypeId
    {
        $this->store->put($this->filePath, $songType->songTypeId->value, $songType);

        return $songType->songTypeId;
    }

    public function delete(SongTypeId $songTypeId): void
    {
        $this->store->unset($this->filePath, $songTypeId->value);
    }
}
