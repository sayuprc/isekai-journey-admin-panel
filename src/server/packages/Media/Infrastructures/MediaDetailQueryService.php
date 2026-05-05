<?php

declare(strict_types=1);

namespace Media\Infrastructures;

use App\Models\Song\SongMediaLink as ModelsSongMediaLink;
use Media\Application\Query\MediaDetailQueryServiceInterface;
use Media\Application\Query\MediaReferencedSong;
use Media\Domain\Models\MediaId;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;

readonly class MediaDetailQueryService implements MediaDetailQueryServiceInterface
{
    public function __construct(private UuidConverterInterface $converter)
    {
    }

    #[Override]
    public function findReferencedSongs(MediaId $mediaId): array
    {
        return array_values(
            ModelsSongMediaLink::query()
                ->with('song')
                ->where('media_id', $this->converter->toBin($mediaId->value))
                ->get()
                ->sortBy([
                    fn (ModelsSongMediaLink $link): int => $link->song->order_no,
                    fn (ModelsSongMediaLink $link): int => $link->order_no,
                ])
                ->map(fn (ModelsSongMediaLink $link): MediaReferencedSong => new MediaReferencedSong(
                    $this->converter->toUuid($link->song_id),
                    $link->song->title,
                    $link->song->order_no,
                    $link->order_no,
                ))
                ->all(),
        );
    }
}
