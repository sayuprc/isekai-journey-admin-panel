<?php

declare(strict_types=1);

namespace Performer\DebugInfrastructures;

use Performer\Domain\Criteria\PerformerSearchCriteria;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Support\Contracts\MapperInterface;
use Support\DebugInfrastructures\Repository\DebugConfig;
use Support\DebugInfrastructures\Repository\JsonFileStore;

readonly class FilePerformerRepository implements PerformerRepositoryInterface
{
    private const string FILE_NAME = 'performers';

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
        $performer = $this->loadAll();

        usort($performer, fn (Performer $a, Performer $b): int => $a->orderNo->value <=> $b->orderNo->value);

        return $performer;
    }

    public function search(PerformerSearchCriteria $criteria): array
    {
        $items = $this->loadAll();

        if ($criteria->name->isPresent()) {
            $items = array_filter($items, fn (Performer $item): bool => str_contains($item->name->value, $criteria->name->get()))
                |> array_values(...);
        }

        if ($criteria->sort->isName()) {
            if ($criteria->order->isAsc()) {
                usort($items, fn (Performer $a, Performer $b): int => $a->name->value <=> $b->name->value);
            } else {
                usort($items, fn (Performer $a, Performer $b): int => $b->name->value <=> $a->name->value);
            }
        } elseif ($criteria->sort->isOrderNo()) {
            if ($criteria->order->isAsc()) {
                usort($items, fn (Performer $a, Performer $b): int => $a->orderNo->value <=> $b->orderNo->value);
            } else {
                usort($items, fn (Performer $a, Performer $b): int => $b->orderNo->value <=> $a->orderNo->value);
            }
        }

        $chunked = array_chunk($items, $criteria->perPage->value);

        return $chunked[$criteria->page - 1] ?? [];
    }

    public function maxPage(PerformerSearchCriteria $criteria): int
    {
        $items = $this->loadAll();

        if ($criteria->name->isPresent()) {
            $items = array_filter($items, fn (Performer $item): bool => str_contains($item->name->value, $criteria->name->get()))
                |> array_values(...);
        }

        return (int)ceil(count($items) / $criteria->perPage->value);
    }

    public function find(PerformerId $performerId): ?Performer
    {
        foreach ($this->loadAll() as $performer) {
            if ($performer->performerId->equals($performerId)) {
                return $performer;
            }
        }

        return null;
    }

    public function findByName(PerformerName $name): ?Performer
    {
        foreach ($this->loadAll() as $performer) {
            if ($performer->name->equals($name)) {
                return $performer;
            }
        }

        return null;
    }

    public function save(Performer $performer): Performer
    {
        $this->store->save(
            $this->filePath,
            $performer->toArray(),
            $this->findIndex($performer->performerId),
        );

        return $performer;
    }

    public function delete(PerformerId $performerId): void
    {
        $index = $this->findIndex($performerId);

        if (is_null($index)) {
            return;
        }

        $this->store->unset($this->filePath, $index);
    }

    public function getMaxOrderNo(): int
    {
        $orderNos = array_map(fn (Performer $item): int => $item->orderNo->value, $this->loadAll());

        return 0 < count($orderNos) ? max($orderNos) : 0;
    }

    /**
     * @return array<Performer>
     */
    private function loadAll(): array
    {
        $class = Performer::class;

        /** @var array<Performer> */
        return $this->mapper->map("array<{$class}>", $this->store->load($this->filePath));
    }

    private function findIndex(PerformerId $performerId): null|int|string
    {
        return array_keys(
            array_filter(
                $this->loadAll(),
                fn (Performer $item): bool => $item->performerId->equals($performerId),
            ),
        )[0] ?? null;
    }
}
