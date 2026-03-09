<?php

declare(strict_types=1);

namespace Song\Application\Assemble;

use Creator\Domain\Models\CreatorRepositoryInterface;
use Song\Domain\Models\Creators\Arranger;
use Song\Domain\Models\Creators\Composer;
use Song\Domain\Models\Creators\Lyricist;
use Song\Domain\Models\Song;

class SongAssembler
{
    public function __construct(private readonly CreatorRepositoryInterface $creatorRepository)
    {
    }

    public function assemble(Song $song): AssembledSong
    {
        // Collect all creator IDs from lyricists, composers, and arrangers
        $allCreatorIds = [];
        $creatorCollections = [$song->lyricists, $song->composers, $song->arrangers];
        foreach ($creatorCollections as $collection) {
            foreach ($collection as $creator) {
                $allCreatorIds[$creator->creatorId->value] = $creator->creatorId;
            }
        }

        // Fetch all creators in a single query if there are any
        $creatorMap = [];
        if (! empty($allCreatorIds)) {
            $creators = $this->creatorRepository->findByIds(...array_values($allCreatorIds));
            foreach ($creators as $creator) {
                $creatorMap[$creator->creatorId->value] = $creator;
            }
        }

        // Create closure to assemble creators using the fetched creator map
        $toAssembled = function (Arranger|Composer|Lyricist $creator) use ($creatorMap): AssembledCreator {
            $found = $creatorMap[$creator->creatorId->value] ?? null;
            // Song Entity が成立している時点で $found が null になることはない
            assert(! is_null($found));

            return new AssembledCreator(
                $creator->creatorId->value,
                $found->name->value,
                $creator->orderNo->value,
            );
        };

        return new AssembledSong(
            $song->songId->value,
            $song->title->value,
            $song->description->value,
            $song->type->getName(),
            $song->type->value,
            $song->attribute?->getName(),
            $song->attribute?->value,
            $song->orderNo->value,
            $song->lyricists->toGeneric()->map($toAssembled)->toArray(),
            $song->composers->toGeneric()->map($toAssembled)->toArray(),
            $song->arrangers->toGeneric()->map($toAssembled)->toArray(),
        );
    }
}
