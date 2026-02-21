<?php

declare(strict_types=1);

namespace Creator\DebugInfrastructures;

use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Models\CreatorRepositoryInterface;
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

    public function all(): array
    {
        $creators = $this->loadAll();

        uasort($creators, fn (Creator $a, Creator $b): int => $a->creatorName->value <=> $b->creatorName->value);

        return $creators;
    }

    public function find(CreatorId $creatorId): ?Creator
    {
        foreach ($this->loadAll() as $creator) {
            if ($creator->creatorId->equals($creatorId)) {
                return $creator;
            }
        }

        return null;
    }

    public function findByName(CreatorName $creatorName): ?Creator
    {
        foreach ($this->loadAll() as $creator) {
            if ($creator->creatorName->equals($creatorName)) {
                return $creator;
            }
        }

        return null;
    }

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

    public function save(Creator $creator): Creator
    {
        $this->store->save(
            $this->filePath,
            $creator->toArray(),
            $this->findIndex($creator->creatorId),
        );

        return $creator;
    }

    public function delete(CreatorId $creatorId): void
    {
        $index = $this->findIndex($creatorId);

        if (is_null($index)) {
            return;
        }

        $this->store->unset($this->filePath, $index);
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
