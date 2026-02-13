<?php

declare(strict_types=1);

namespace Song\DebugInfrastructures;

use Creator\Domain\Models\CreatorId;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Support\Contracts\MapperInterface;
use Support\DebugInfrastructures\Repository\DebugConfig;
use Support\DebugInfrastructures\Repository\JsonFileStore;

readonly class FileSongRepository implements SongRepositoryInterface
{
    private const string FILE_NAME = 'songs';

    private string $filePath;

    public function __construct(
        private MapperInterface $mapper,
        private JsonFileStore $store,
        DebugConfig $config,
    ) {
        $this->filePath = $config->path . '/' . self::FILE_NAME;
    }

    public function all(): array
    {
        $songs = $this->loadAll();

        uasort($songs, fn (Song $a, Song $b): int => $a->orderNo->value <=> $b->orderNo->value);

        return $songs;
    }

    public function find(SongId $songId): ?Song
    {
        foreach ($this->loadAll() as $song) {
            if ($song->songId->value === $songId->value) {
                return $song;
            }
        }

        return null;
    }

    public function isCreatorUsed(CreatorId $creatorId): bool
    {
        foreach ($this->loadAll() as $song) {
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
        $this->store->save(
            $this->filePath,
            $song->toArray(),
            $this->findIndex($song->songId),
        );

        return $song;
    }

    public function delete(SongId $songId): void
    {
        $index = $this->findIndex($songId);

        if (is_null($index)) {
            return;
        }

        $this->store->unset($this->filePath, $index);
    }

    public function getMaxOrderNo(): int
    {
        $songs = $this->loadAll();

        uasort($songs, fn (Song $a, Song $b): int => $b->orderNo->value <=> $a->orderNo->value);

        return array_first($songs)->orderNo->value ?? 0;
    }

    /**
     * @return array<Song>
     */
    private function loadAll(): array
    {
        $class = Song::class;

        /** @var array<Song> */
        return $this->mapper->map("array<{$class}>", $this->store->load($this->filePath));
    }

    private function findIndex(SongId $songId): null|int|string
    {
        return array_keys(
            array_filter(
                $this->loadAll(),
                fn (Song $item): bool => $item->songId->value === $songId->value,
            ),
        )[0] ?? null;
    }
}
