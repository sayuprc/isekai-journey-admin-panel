<?php

declare(strict_types=1);

namespace Song\Infrastructures;

use App\Models\Song\Song as ModelsSong;
use App\Models\Song\SongPerson as ModelsSongPerson;
use App\Models\Song\SongTagging;
use Creator\Domain\Models\CreatorId;
use Override;
use Person\Domain\Models\PersonId;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class SongRepository implements SongRepositoryInterface
{
    public function __construct(
        private UuidConverterInterface $converter,
        private SongTagRepositoryInterface $songTagRepository,
    ) {
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
    public function isPersonUsed(PersonId $personId): bool
    {
        return ModelsSongPerson::query()
            ->where('person_id', $this->converter->toBin($personId->value))
            ->exists();
    }

    #[Override]
    public function isCreatorUsed(CreatorId $creatorId): bool
    {
        return ModelsSongPerson::query()
            ->where('person_id', $this->converter->toBin($creatorId->value))
            ->exists();
    }

    #[Override]
    public function save(Song $song): Song
    {
        $id = $this->converter->toBin($song->songId->value);
        $data = $song->toArray();

        ModelsSongPerson::query()->where('song_id', $id)->delete();
        SongTagging::query()->where('song_id', $id)->delete();

        ModelsSong::query()->upsert(
            [
                'song_id' => $id,
                'title' => $data['title'],
                'description' => $data['description'],
                'lyrics_link' => $data['lyrics_link'],
                'type' => $data['type'],
                'is_display' => $data['is_display'],
                'order_no' => $data['order_no'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            ['song_id'],
            [
                'title',
                'description',
                'lyrics_link',
                'type',
                'is_display',
                'order_no',
                'updated_at',
            ],
        );

        $persons = array_map(fn (array $row) => $this->toPersonRecord($id, $row), $data['persons']);
        $tags = array_map(fn (array $row) => $this->toTaggingRecord($id, $row), $data['tags']);

        if ($persons !== []) {
            ModelsSongPerson::query()->insert($persons);
        }

        if ($tags !== []) {
            SongTagging::query()->insert($tags);
        }

        return $this->find($song->songId) ?? $song;
    }

    /**
     * @param array{person_id: string, role: string, order_no: int} $row
     *
     * @return array{song_id: string, person_id: string, role: string, order_no: int}
     */
    private function toPersonRecord(string $binId, array $row): array
    {
        return [
            'song_id' => $binId,
            'person_id' => $this->converter->toBin($row['person_id']),
            'role' => $row['role'],
            'order_no' => $row['order_no'],
        ];
    }

    /**
     * @param array{song_tag_id: string} $row
     *
     * @return array{song_id: string, song_tag_id: string}
     */
    private function toTaggingRecord(string $binId, array $row): array
    {
        return [
            'song_id' => $binId,
            'song_tag_id' => $this->converter->toBin($row['song_tag_id']),
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
        $fn = fn (ModelsSongPerson $row): array => [
            'personId' => $this->converter->toUuid($row->person_id),
            'role' => $row->role,
            'orderNo' => $row->order_no,
        ];
        $toTag = fn (SongTagging $row): array => [
            'songTagId' => $this->converter->toUuid($row->song_tag_id),
        ];

        /** @var list<array{personId: string, role: string, orderNo: int}> */
        $persons = $model->persons->sortBy('order_no')->map($fn)->values()->all();
        /** @var list<array{songTagId: string}> */
        $tags = $this->sortTagsByMasterOrder($model->taggings->map($toTag)->all() |> array_values(...));

        return Song::reconstruct(
            $this->converter->toUuid($model->song_id),
            $model->title,
            $model->description,
            $model->lyrics_link,
            $model->type,
            $model->is_display,
            $model->order_no,
            $tags,
            $persons,
        );
    }

    /**
     * @param list<array{songTagId: string}> $tags
     *
     * @return list<array{songTagId: string}>
     */
    private function sortTagsByMasterOrder(array $tags): array
    {
        if ($tags === []) {
            return [];
        }

        $foundTags = $this->songTagRepository->findByIds(
            ...array_map(
                fn (array $tag): SongTagId => SongTagId::reconstruct($tag['songTagId']),
                $tags,
            ),
        );

        return array_map(
            fn (SongTag $tag): array => ['songTagId' => $tag->songTagId->value],
            $foundTags,
        ) |> array_values(...);
    }
}
