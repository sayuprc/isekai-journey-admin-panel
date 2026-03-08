<?php

declare(strict_types=1);

namespace Performer\Infrastructures;

use App\Models\Performer\Performer as ModelsPerformer;
use Performer\Domain\Criteria\PerformerSearchCriteria;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class PerformerRepository implements PerformerRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    public function all(): array
    {
        return ModelsPerformer::query()
            ->orderBy('order_no')
            ->get()
            ->map($this->hydrate(...))
            ->all();
    }

    public function search(PerformerSearchCriteria $criteria): array
    {
        $query = ModelsPerformer::query();

        if ($criteria->name->isPresent()) {
            $query = $query->whereLike('name', '%' . $this->likeEscape($criteria->name->get()) . '%');
        }

        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        return $query->orderBy($criteria->sort->value, $criteria->order->value)
            ->limit($criteria->perPage->value)
            ->offset($offset)
            ->get()
            ->map($this->hydrate(...))
            ->all();
    }

    public function maxPage(PerformerSearchCriteria $criteria): int
    {
        $query = ModelsPerformer::query();

        if ($criteria->name->isPresent()) {
            $query = $query->whereLike('name', '%' . $this->likeEscape($criteria->name->get()) . '%');
        }

        return (int)ceil($query->count() / $criteria->perPage->value);
    }

    private function likeEscape(string $keyword): string
    {
        return addcslashes($keyword, '%_\\');
    }

    public function find(PerformerId $performerId): ?Performer
    {
        $found = ModelsPerformer::query()
            ->where('performer_id', $this->converter->toBin($performerId->value))
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    public function findByName(PerformerName $name): ?Performer
    {
        $found = ModelsPerformer::query()
            ->where('name', $name->value)
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    public function save(Performer $performer): Performer
    {
        ModelsPerformer::query()->upsert(
            [
                ...$performer->toArray(),
                'performer_id' => $this->converter->toBin($performer->performerId->value),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            ['performer_id'],
            [
                'name',
                'order_no',
                'updated_at',
            ],
        );

        return $performer;
    }

    public function delete(PerformerId $performerId): void
    {
        ModelsPerformer::query()->where('performer_id', $this->converter->toBin($performerId->value))->delete();
    }

    public function getMaxOrderNo(): int
    {
        /** @var int */
        return ModelsPerformer::query()->max('order_no') ?? 0;
    }

    private function hydrate(ModelsPerformer $row): Performer
    {
        return Performer::reconstruct(
            $this->converter->toUuid($row->performer_id),
            $row->name,
            $row->order_no,
        );
    }
}
