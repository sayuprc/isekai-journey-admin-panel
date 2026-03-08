<?php

declare(strict_types=1);

namespace Song\DebugInfrastructures;

use Creator\Domain\Models\CreatorId;
use Song\Domain\Criteria\SongSearchCriteria;
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

        usort($songs, fn (Song $a, Song $b): int => $a->orderNo->value <=> $b->orderNo->value);

        return $songs;
    }

    public function search(SongSearchCriteria $criteria): array
    {
        $items = $this->loadAll();

        if ($criteria->title->isPresent()) {
            $items = array_filter($items, fn (Song $item): bool => str_contains($item->title->value, $criteria->title->get()))
                |> array_values(...);
        }

        if ($criteria->type->isPresent()) {
            $items = array_filter($items, fn (Song $item): bool => $item->type === $criteria->type->get())
                |> array_values(...);
        }

        if ($criteria->attribute->isPresent()) {
            $items = array_filter($items, fn (Song $item): bool => $item->attribute === $criteria->attribute->get())
                |> array_values(...);
        }

        if ($criteria->sort->isTitle()) {
            if ($criteria->order->isAsc()) {
                usort($items, fn (Song $a, Song $b): int => $a->title->value <=> $b->title->value);
            } else {
                usort($items, fn (Song $a, Song $b): int => $b->title->value <=> $a->title->value);
            }
        } elseif ($criteria->sort->isOrderNo()) {
            if ($criteria->order->isAsc()) {
                usort($items, fn (Song $a, Song $b): int => $a->orderNo->value <=> $b->orderNo->value);
            } else {
                usort($items, fn (Song $a, Song $b): int => $b->orderNo->value <=> $a->orderNo->value);
            }
        }

        $chunked = array_chunk($items, $criteria->perPage->value);

        return $chunked[$criteria->page - 1] ?? [];
    }

    public function maxPage(SongSearchCriteria $criteria): int
    {
        $items = $this->loadAll();

        if ($criteria->title->isPresent()) {
            $items = array_filter($items, fn (Song $item): bool => str_contains($item->title->value, $criteria->title->get()))
                |> array_values(...);
        }

        if ($criteria->type->isPresent()) {
            $items = array_filter($items, fn (Song $item): bool => $item->type === $criteria->type->get())
                |> array_values(...);
        }

        if ($criteria->attribute->isPresent()) {
            $items = array_filter($items, fn (Song $item): bool => $item->attribute === $criteria->attribute->get())
                |> array_values(...);
        }

        return (int)ceil(count($items) / $criteria->perPage->value);
    }

    public function find(SongId $songId): ?Song
    {
        foreach ($this->loadAll() as $song) {
            if ($song->songId->equals($songId)) {
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
                    if ($item->creatorId->equals($creatorId)) {
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
        $orderNos = array_map(fn (Song $item): int => $item->orderNo->value, $this->loadAll());

        return 0 < count($orderNos) ? max($orderNos) : 0;
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
                fn (Song $item): bool => $item->songId->equals($songId),
            ),
        )[0] ?? null;
    }
}
