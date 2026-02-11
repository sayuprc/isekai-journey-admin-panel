<?php

declare(strict_types=1);

namespace Song\DebugInfrastructures;

use Creator\Domain\Models\CreatorId;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Support\DebugInfrastructures\Repository\DebugConfig;
use Support\DebugInfrastructures\Repository\FileStore;

readonly class FileSongRepository implements SongRepositoryInterface
{
    private const string FILE_NAME = 'songs.dat';

    private string $filePath;

    /**
     * @param FileStore<Song> $store
     */
    public function __construct(
        private FileStore $store,
        DebugConfig $config,
    ) {
        $this->filePath = $config->path . '/' . self::FILE_NAME;
    }

    public function all(): array
    {
        return array_values($this->store->getAll($this->filePath));
    }

    public function find(SongId $songId): ?Song
    {
        return $this->store->get($this->filePath, $songId->value);
    }

    public function isCreatorUsed(CreatorId $creatorId): bool
    {
        foreach ($this->all() as $song) {
            foreach ([$song->arrangers, $song->composers, $song->lyricists] as $items) {
                foreach ($items as $item) {
                    if ($item->creatorId->value === $creatorId->value) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function save(Song $song): Song
    {
        $this->store->put($this->filePath, $song->songId->value, $song);

        return $song;
    }

    public function delete(SongId $songId): void
    {
        $this->store->unset($this->filePath, $songId->value);
    }

    public function getMaxOrderNo(): int
    {
        $songs = $this->all();

        uasort($songs, fn (Song $a, Song $b): int => $b->orderNo->value <=> $a->orderNo->value);

        return array_first($songs)->orderNo->value ?? 0;
    }
}
