<?php

declare(strict_types=1);

namespace Song\Infrastructures;

use App\Models\Song\Song as ModelsSong;
use App\Models\Song\SongArranger;
use App\Models\Song\SongComposer;
use App\Models\Song\SongLyricist;
use Creator\Domain\Models\CreatorId;
use Override;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class SongRepository implements SongRepositoryInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
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

    #[Override]
    public function isCreatorUsed(CreatorId $creatorId): bool
    {
        $id = $this->converter->toBin($creatorId->value);

        return SongLyricist::query()->where('creator_id', $id)->exists()
            || SongComposer::query()->where('creator_id', $id)->exists()
            || SongArranger::query()->where('creator_id', $id)->exists();
    }

    #[Override]
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
                'is_display' => $data['is_display'],
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
                'is_display',
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

    #[Override]
    public function delete(SongId $songId): void
    {
        ModelsSong::query()->where('song_id', $this->converter->toBin($songId->value))->delete();
    }

    #[Override]
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
            $model->is_display,
            $model->attribute,
        );
    }
}
