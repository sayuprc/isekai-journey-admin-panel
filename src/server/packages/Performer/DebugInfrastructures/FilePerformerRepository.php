<?php

declare(strict_types=1);

namespace Performer\DebugInfrastructures;

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

        uasort($performer, fn (Performer $a, Performer $b): int => $a->orderNo->value <=> $b->orderNo->value);

        return $performer;
    }

    public function find(PerformerId $performerId): ?Performer
    {
        foreach ($this->loadAll() as $performer) {
            if ($performer->performerId->value === $performerId->value) {
                return $performer;
            }
        }

        return null;
    }

    public function findByName(PerformerName $performerName): ?Performer
    {
        foreach ($this->loadAll() as $performer) {
            if ($performer->performerName->value === $performerName->value) {
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
        $performers = $this->loadAll();

        uasort($performers, fn (Performer $a, Performer $b): int => $b->orderNo->value <=> $a->orderNo->value);

        return array_first($performers)->orderNo->value ?? 0;
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
                fn (Performer $item): bool => $item->performerId->value === $performerId->value,
            ),
        )[0] ?? null;
    }
}
