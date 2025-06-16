<?php

declare(strict_types=1);

namespace Song\Infrastructures\Repositories;

use Song\Domain\Models\Song;
use Song\Domain\Repositories\SongRepositoryInterface;
use Support\Contracts\ConfigInterface;
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
    public function all(): array
    {
        return array_values($this->store->getAll($this->filePath));
    }

    public function insert(Song $song): void
    {
        $this->store->put($this->filePath, $song->songId->value, $song);
    }
}
