<?php

declare(strict_types=1);

namespace Creator\Infrastructures;

use App\Models\Creator\Creator as ModelsCreator;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class CreatorRepository implements CreatorRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    public function all(): array
    {
        return ModelsCreator::query()
            ->orderBy('order_no')
            ->get()
            ->map($this->hydrate(...))
            ->all();
    }

    public function find(CreatorId $creatorId): ?Creator
    {
        $found = ModelsCreator::query()
            ->where('creator_id', $this->converter->toBin($creatorId->value))
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    public function findByName(CreatorName $name): ?Creator
    {
        $found = ModelsCreator::query()
            ->where('name', $name->value)
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    public function findByIds(CreatorId ...$creatorIds): array
    {
        return ModelsCreator::query()
            ->whereIn(
                'creator_id',
                array_map(fn (CreatorId $creatorId): string => $this->converter->toBin($creatorId->value), $creatorIds),
            )
            ->get()
            ->map($this->hydrate(...))
            ->all();
    }

    public function save(Creator $creator): Creator
    {
        ModelsCreator::query()->upsert(
            [
                ...$creator->toArray(),
                'creator_id' => $this->converter->toBin($creator->creatorId->value),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            ['creator_id'],
            [
                'name',
                'order_no',
                'updated_at',
            ],
        );

        return $creator;
    }

    public function delete(CreatorId $creatorId): void
    {
        ModelsCreator::query()->where('creator_id', $this->converter->toBin($creatorId->value))->delete();
    }

    public function getMaxOrderNo(): int
    {
        /** @var int */
        return ModelsCreator::query()->max('order_no') ?? 0;
    }

    private function hydrate(ModelsCreator $row): Creator
    {
        return Creator::reconstruct(
            $this->converter->toUuid($row->creator_id),
            $row->name,
            $row->order_no,
        );
    }
}
