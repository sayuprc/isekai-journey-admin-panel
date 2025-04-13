<?php

declare(strict_types=1);

namespace Song\Infrastructures\Repositories;

use Song\Domain\Models\Song;
use Song\Domain\Repositories\SongRepositoryInterface;
use Support\Config\ConfigInterface;
use Support\Repository\FileStore;

class FileSongRepository implements SongRepositoryInterface
{
    private const string FILE_NAME = 'songs.dat';

    private readonly string $filePath;

    /**
     * @param FileStore<Song> $store
     */
    public function __construct(
        private readonly FileStore $store,
        private readonly ConfigInterface $config,
    ) {
        $this->filePath = $this->config->getString('debug.file.path') . '/' . self::FILE_NAME;
    }

    /**
     * @return array<Song>
     */
    public function listSongs(): array
    {
        return array_values($this->store->getAll($this->filePath));
    }
}
