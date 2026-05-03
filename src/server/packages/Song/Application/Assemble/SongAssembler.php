<?php

declare(strict_types=1);

namespace Song\Application\Assemble;

use Creator\Domain\Models\CreatorRepositoryInterface;
use Song\Domain\Models\Creators\Arranger;
use Song\Domain\Models\Creators\Composer;
use Song\Domain\Models\Creators\Lyricist;
use Song\Domain\Models\Song;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Song\Domain\Models\Tags\SongTagReference;

class SongAssembler
{
    public function __construct(
        private readonly CreatorRepositoryInterface $creatorRepository,
        private readonly SongTagRepositoryInterface $songTagRepository,
    ) {
    }

    public function assemble(Song $song): AssembledSong
    {
        $allCreatorIds = [];
        foreach ([$song->lyricists, $song->composers, $song->arrangers] as $collection) {
            foreach ($collection as $creator) {
                $allCreatorIds[$creator->creatorId->value] = $creator->creatorId;
            }
        }

        $creatorMap = [];
        if (! empty($allCreatorIds)) {
            $creators = $this->creatorRepository->findByIds(...array_values($allCreatorIds));
            foreach ($creators as $creator) {
                $creatorMap[$creator->creatorId->value] = $creator;
            }
        }

        $allSongTagIds = [];
        foreach ($song->tags as $tag) {
            $allSongTagIds[$tag->songTagId->value] = $tag->songTagId;
        }

        $songTagMap = [];
        if (! empty($allSongTagIds)) {
            $tags = $this->songTagRepository->findByIds(...array_values($allSongTagIds));
            foreach ($tags as $tag) {
                $songTagMap[$tag->songTagId->value] = $tag;
            }
        }

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
        $toAssembledTag = function (SongTagReference $tag) use ($songTagMap): AssembledTag {
            $found = $songTagMap[$tag->songTagId->value] ?? null;
            // Song Entity が成立している時点で $found が null になることはない
            assert(! is_null($found));

            return new AssembledTag(
                $tag->songTagId->value,
                $found->name->value,
            );
        };

        return new AssembledSong(
            $song->songId->value,
            $song->title->value,
            $song->description->value,
            $song->type->getName(),
            $song->type->value,
            $song->isDisplay,
            $song->orderNo->value,
            $song->lyricists->toGeneric()->map($toAssembled)->toArray(),
            $song->composers->toGeneric()->map($toAssembled)->toArray(),
            $song->arrangers->toGeneric()->map($toAssembled)->toArray(),
            $song->tags->toGeneric()->map($toAssembledTag)->toArray(),
        );
    }
}
