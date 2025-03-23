<?php

declare(strict_types=1);

namespace Song\Infrastructures\Repositories;

use Shared\Repository\FileStore;
use Song\Domain\Entities\Song;
use Song\Domain\Repositories\SongRepositoryInterface;

class FileSongRepository implements SongRepositoryInterface
{
    private const string PATH = 'songs.dat';

    /**
     * @param FileStore<Song> $store
     */
    public function __construct(private readonly FileStore $store)
    {
    }

    /**
     * @return array<Song>
     */
    public function listSongs(): array
    {
        return array_values($this->store->getAll(self::PATH));
    }
}
