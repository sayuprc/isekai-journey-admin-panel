<?php

declare(strict_types=1);

namespace SongType\Infrastructures\Repositories;

use SongType\Domain\Models\SongType;
use SongType\Domain\Models\SongTypeName;
use SongType\Domain\Models\SongTypeRepositoryInterface;
use Support\Config\ConfigInterface;
use Support\Repository\FileStore;

class FileSongTypeRepository implements SongTypeRepositoryInterface
{
    private const string FILE_NAME = 'song-types.dat';

    private readonly string $filePath;

    /**
     * @param FileStore<SongType> $store
     */
    public function __construct(
        private readonly FileStore $store,
        private readonly ConfigInterface $config,
    ) {
        $this->filePath = $this->config->getString('debug.file.path') . '/' . self::FILE_NAME;
    }

    /**
     * @return array<SongType>
     */
    public function all(): array
    {
        return array_values($this->store->getAll($this->filePath));
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
}
