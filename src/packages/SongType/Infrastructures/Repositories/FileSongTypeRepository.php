<?php

declare(strict_types=1);

namespace SongType\Infrastructures\Repositories;

use SongType\Domain\Models\SongType;
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
}
