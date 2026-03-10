<?php

declare(strict_types=1);

namespace Creator\DebugInfrastructures;

use Creator\Domain\Criteria\CreatorSearchCriteria;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Override;
use Support\Contracts\MapperInterface;
use Support\DebugInfrastructures\Repository\DebugConfig;
use Support\DebugInfrastructures\Repository\JsonFileStore;
readonly class FileCreatorRepository implements CreatorRepositoryInterface
{
    private const string FILE_NAME = 'creators';

    private string $filePath;

    public function __construct(
        private MapperInterface $mapper,
        private JsonFileStore $store,
        DebugConfig $config,
    ) {
        $this->filePath = $config->path . '/' . self::FILE_NAME;
    }

    #[Override]
    public function all(): array
    {
        $creators = $this->loadAll();

        usort($creators, fn (Creator $a, Creator $b): int => $a->orderNo->value <=> $b->orderNo->value);

        return $creators;
    }

    #[Override]
    public function search(CreatorSearchCriteria $criteria): array
    {
        $items = $this->loadAll();

        if ($criteria->name->isPresent()) {
            $items = array_filter($items, fn (Creator $item): bool => str_contains($item->name->value, $criteria->name->get()))
                |> array_values(...);
        }

        if ($criteria->sort->isName()) {
            if ($criteria->order->isAsc()) {
                usort($items, fn (Creator $a, Creator $b): int => $a->name->value <=> $b->name->value);
            } else {
                usort($items, fn (Creator $a, Creator $b): int => $b->name->value <=> $a->name->value);
            }
        } elseif ($criteria->sort->isOrderNo()) {
            if ($criteria->order->isAsc()) {
                usort($items, fn (Creator $a, Creator $b): int => $a->orderNo->value <=> $b->orderNo->value);
            } else {
                usort($items, fn (Creator $a, Creator $b): int => $b->orderNo->value <=> $a->orderNo->value);
            }
        }

        $chunked = array_chunk($items, $criteria->perPage->value);

        return $chunked[$criteria->page - 1] ?? [];
    }

    #[Override]
    public function maxPage(CreatorSearchCriteria $criteria): int
    {
        $items = $this->loadAll();

        if ($criteria->name->isPresent()) {
            $items = array_filter($items, fn (Creator $item): bool => str_contains($item->name->value, $criteria->name->get()))
                |> array_values(...);
        }

        return (int)ceil(count($items) / $criteria->perPage->value);
    }

    #[Override]
    public function find(CreatorId $creatorId): ?Creator
    {
        foreach ($this->loadAll() as $creator) {
            if ($creator->creatorId->equals($creatorId)) {
                return $creator;
            }
        }

        return null;
    }

    #[Override]
    public function findByName(CreatorName $name): ?Creator
    {
        foreach ($this->loadAll() as $creator) {
            if ($creator->name->equals($name)) {
                return $creator;
            }
        }

        return null;
    }

    #[Override]
    public function findByIds(CreatorId ...$creatorIds): array
    {
        $founds = [];
        $creators = $this->loadAll();

        foreach ($creatorIds as $creatorId) {
            foreach ($creators as $creator) {
                if ($creator->creatorId->equals($creatorId)) {
                    $founds[] = $creator;

                    break;
                }
            }
        }

        return $founds;
    }

    #[Override]
    public function save(Creator $creator): Creator
    {
        $this->store->save(
            $this->filePath,
            $creator->toArray(),
            $this->findIndex($creator->creatorId),
        );

        return $creator;
    }

    #[Override]
    public function delete(CreatorId $creatorId): void
    {
        $index = $this->findIndex($creatorId);

        if (is_null($index)) {
            return;
        }

        $this->store->unset($this->filePath, $index);
    }

    #[Override]
    public function getMaxOrderNo(): int
    {
        $orderNos = array_map(fn (Creator $item): int => $item->orderNo->value, $this->loadAll());

        return 0 < count($orderNos) ? max($orderNos) : 0;
    }

    /**
     * @return array<Creator>
     */
    private function loadAll(): array
    {
        $class = Creator::class;

        /** @var array<Creator> */
        return $this->mapper->map("array<{$class}>", $this->store->load($this->filePath));
    }

    private function findIndex(CreatorId $creatorId): null|int|string
    {
        return array_keys(
            array_filter(
                $this->loadAll(),
                fn (Creator $item): bool => $item->creatorId->equals($creatorId),
            ),
        )[0] ?? null;
    }
}
