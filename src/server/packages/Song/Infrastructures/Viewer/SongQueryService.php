<?php

declare(strict_types=1);

namespace Song\Infrastructures\Viewer;

use App\Models\Song\Song;
use App\Models\Song\SongPerson;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;
use Song\Application\Viewer\Query\SongListCursor;
use Song\Application\Viewer\Query\SongListItem;
use Song\Application\Viewer\Query\SongListPage;
use Song\Application\Viewer\Query\SongQueryServiceInterface;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\SongType;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class SongQueryService implements SongQueryServiceInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function list(?string $cursor, int $limit): SongListPage
    {
        $query = Song::query()
            ->select(['song_id', 'title', 'description', 'type', 'order_no'])
            ->where('is_display', true)
            // 将来的に Eloquent やめるので黙らせる
            // @phpstan-ignore-next-line
            ->with([
                'persons' => fn (HasMany $query) => $query
                    ->select(['song_id', 'person_id', 'role', 'order_no'])
                    ->orderBy('order_no'),
                'persons.person' => fn (BelongsTo $query) => $query
                    ->select(['person_id', 'name']),
            ])
            ->withCount([
                'songMediaLinks as media_count' => fn (Builder $query) => $query
                    ->whereHas('media', fn (Builder $mediaQuery) => $mediaQuery->where('is_display', true)),
                'releases as release_count' => fn (Builder $query) => $query
                    ->where('is_display', true),
            ])
            ->orderBy('order_no')
            ->orderBy('song_id');

        if (is_string($cursor)) {
            $decoded = SongListCursor::decode($cursor);

            $query->where(
                fn (Builder $builder) => $builder
                    ->where('order_no', '>', $decoded->orderNo)
                    ->orWhere(fn (Builder $sameOrder) => $sameOrder
                        ->where('order_no', $decoded->orderNo)
                        ->where('song_id', '>', $this->converter->toBin($decoded->songId))),
            );
        }

        $songs = $query
            ->limit($limit + 1)
            ->get()
            ->map(function (Song $song): SongListItem {
                // 直接 $song から取得しようとすると静的解析でエラーになるため回避策として getAttribute を呼び出している
                // 将来的に Eloquent をやめてこの回避策をしなくてもいいようにする
                $releaseCount = $song->getAttribute('release_count');
                $mediaCount = $song->getAttribute('media_count');

                return new SongListItem(
                    $this->converter->toUuid($song->song_id),
                    $song->title,
                    SongType::from($song->type),
                    $song->description,
                    $this->personNamesByRole($song, SongPersonRole::Lyricist),
                    $this->personNamesByRole($song, SongPersonRole::Composer),
                    $this->personNamesByRole($song, SongPersonRole::Arranger),
                    is_numeric($releaseCount) ? (int)$releaseCount : 0,
                    is_numeric($mediaCount) ? (int)$mediaCount : 0,
                    $song->order_no,
                );
            });

        $hasNextPage = $songs->count() > $limit;
        $currentSongs = $hasNextPage ? $songs->slice(0, $limit) : $songs;
        $lastSong = $hasNextPage ? $currentSongs->last() : null;

        $nextCursor = is_null($lastSong)
            ? null
            : SongListCursor::encode($lastSong->orderNo, $lastSong->songId);

        return new SongListPage($currentSongs->all(), $nextCursor);
    }

    /**
     * @return array<string>
     */
    private function personNamesByRole(Song $song, SongPersonRole $role): array
    {
        return $song->persons
            ->toBase()
            ->filter(fn (SongPerson $person): bool => (int)$person->role === $role->value)
            ->map(fn (SongPerson $person): string => $person->person->name)
            ->values()
            ->all();
    }
}
