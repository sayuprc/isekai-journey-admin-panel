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
use Support\Infrastructures\Database\SqlHelper;

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
            // 前方一致検索でインデックスを活用
            // 中間一致が必要な場合は、外部の検索エンジン（Elasticsearch など）を利用すること
            $keyword = SqlHelper::escapeLike(mb_strtolower($criteria->name->get()));
            $query = $query->whereLike('name_lower', $keyword . '%');
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
            // 前方一致検索でインデックスを活用
            // 中間一致が必要な場合は、外部の検索エンジン（Elasticsearch など）を利用すること
            $keyword = SqlHelper::escapeLike(mb_strtolower($criteria->name->get()));
            $query = $query->whereLike('name_lower', $keyword . '%');
        }

        return (int)ceil($query->count() / $criteria->perPage->value);
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
