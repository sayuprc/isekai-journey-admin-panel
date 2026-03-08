<?php

declare(strict_types=1);

namespace Song\Infrastructures;

use App\Models\Song\Song as ModelsSong;
use App\Models\Song\SongArranger;
use App\Models\Song\SongComposer;
use App\Models\Song\SongLyricist;
use Creator\Domain\Models\CreatorId;
use Song\Domain\Criteria\SongSearchCriteria;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\SqlHelper;

readonly class SongRepository implements SongRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    public function all(): array
    {
        return ModelsSong::query()
            ->orderBy('order_no')
            ->get()
            ->map($this->hydrate(...))
            ->all();
    }

    public function search(SongSearchCriteria $criteria): array
    {
        $query = ModelsSong::query();

        if ($criteria->title->isPresent()) {
            $query = $query->whereLike('title', '%' . SqlHelper::escapeLike($criteria->title->get()) . '%');
        }

        if ($criteria->type->isPresent()) {
            $query = $query->where('type', $criteria->type->get());
        }

        if ($criteria->attribute->isPresent()) {
            $query = $query->where('attribute', $criteria->attribute->get());
        }

        $offset = ($criteria->page - 1) * $criteria->perPage->value;

        return $query->orderBy($criteria->sort->value, $criteria->order->value)
            ->limit($criteria->perPage->value)
            ->offset($offset)
            ->get()
            ->map($this->hydrate(...))
            ->all();
    }

    public function maxPage(SongSearchCriteria $criteria): int
    {
        $query = ModelsSong::query();

        if ($criteria->title->isPresent()) {
            $query = $query->whereLike('title', '%' . SqlHelper::escapeLike($criteria->title->get()) . '%');
        }

        if ($criteria->type->isPresent()) {
            $query = $query->where('type', $criteria->type->get());
        }

        if ($criteria->attribute->isPresent()) {
            $query = $query->where('attribute', $criteria->attribute->get());
        }

        return (int)ceil($query->count() / $criteria->perPage->value);
    }

    public function find(SongId $songId): ?Song
    {
        $found = ModelsSong::query()
            ->where('song_id', $this->converter->toBin($songId->value))
            ->first();

        if (is_null($found)) {
            return null;
        }

        return $this->hydrate($found);
    }

    public function isCreatorUsed(CreatorId $creatorId): bool
    {
        $id = $this->converter->toBin($creatorId->value);

        return SongLyricist::query()->where('creator_id', $id)->exists()
            || SongComposer::query()->where('creator_id', $id)->exists()
            || SongArranger::query()->where('creator_id', $id)->exists();
    }

    public function save(Song $song): Song
    {
        $id = $this->converter->toBin($song->songId->value);
        $data = $song->toArray();

        SongLyricist::query()->where('song_id', $id)->delete();
        SongComposer::query()->where('song_id', $id)->delete();
        SongArranger::query()->where('song_id', $id)->delete();

        ModelsSong::query()->upsert(
            [
                'song_id' => $id,
                'title' => $data['title'],
                'description' => $data['description'],
                'type' => $data['type'],
                'attribute' => $data['attribute'],
                'order_no' => $data['order_no'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            ['song_id'],
            [
                'title',
                'description',
                'type',
                'attribute',
                'order_no',
                'updated_at',
            ],
        );

        $lyricists = array_map(fn (array $row) => $this->toRecord($id, $row), $data['lyricists']);
        $composers = array_map(fn (array $row) => $this->toRecord($id, $row), $data['composers']);
        $arrangers = array_map(fn (array $row) => $this->toRecord($id, $row), $data['arrangers']);

        if ($lyricists !== []) {
            SongLyricist::query()->insert($lyricists);
        }

        if ($composers !== []) {
            SongComposer::query()->insert($composers);
        }

        if ($arrangers !== []) {
            SongArranger::query()->insert($arrangers);
        }

        return $song;
    }

    /**
     * @param array{creator_id: string, order_no: int} $row
     *
     * @return array{song_id: string, creator_id: string, order_no: int}
     */
    private function toRecord(string $binId, array $row): array
    {
        return [
            'song_id' => $binId,
            'creator_id' => $this->converter->toBin($row['creator_id']),
            'order_no' => $row['order_no'],
        ];
    }

    public function delete(SongId $songId): void
    {
        ModelsSong::query()->where('song_id', $this->converter->toBin($songId->value))->delete();
    }

    public function getMaxOrderNo(): int
    {
        /** @var int */
        return ModelsSong::query()->max('order_no') ?? 0;
    }

    private function hydrate(ModelsSong $model): Song
    {
        $fn = fn (SongArranger|SongComposer|SongLyricist $row): array => [
            'creatorId' => $this->converter->toUuid($row->creator_id),
            'orderNo' => $row->order_no,
        ];

        /** @var list<array{creatorId: string, orderNo: int}> */
        $lyricists = $model->lyricists->map($fn)->all();
        /** @var list<array{creatorId: string, orderNo: int}> */
        $composers = $model->composers->map($fn)->all();
        /** @var list<array{creatorId: string, orderNo: int}> */
        $arrangers = $model->arrangers->map($fn)->all();

        return Song::reconstruct(
            $this->converter->toUuid($model->song_id),
            $model->title,
            $model->description,
            $model->type,
            $model->order_no,
            $lyricists,
            $composers,
            $arrangers,
            $model->attribute,
        );
    }
}
