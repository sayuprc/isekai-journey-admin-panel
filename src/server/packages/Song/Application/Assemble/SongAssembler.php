<?php

declare(strict_types=1);

namespace Song\Application\Assemble;

use Person\Domain\Models\PersonRepositoryInterface;
use Song\Domain\Models\Persons\SongPerson;
use Song\Domain\Models\Song;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Song\Domain\Models\Tags\SongTagReference;

class SongAssembler
{
    public function __construct(
        private readonly PersonRepositoryInterface $personRepository,
        private readonly SongTagRepositoryInterface $songTagRepository,
    ) {
    }

    public function assemble(Song $song): AssembledSong
    {
        $allPersonIds = [];
        foreach ($song->persons as $person) {
            $allPersonIds[$person->personId->value] = $person->personId;
        }

        $personMap = [];
        if (! empty($allPersonIds)) {
            $persons = $this->personRepository->findByIds(...array_values($allPersonIds));
            foreach ($persons as $person) {
                $personMap[$person->personId->value] = $person;
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

        $toAssembled = function (SongPerson $person) use ($personMap): AssembledPerson {
            $found = $personMap[$person->personId->value] ?? null;
            // Song Entity が成立している時点で $found が null になることはない
            assert(! is_null($found));

            return new AssembledPerson(
                $person->personId->value,
                $found->name->value,
                $person->role,
                $person->orderNo->value,
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
            $song->lyricsLink?->value,
            $song->type->getName(),
            $song->type->value,
            $song->isDisplay,
            $song->orderNo->value,
            $song->persons->toGeneric()->map($toAssembled)->toArray(),
            $song->tags->toGeneric()->map($toAssembledTag)->toArray(),
        );
    }
}
