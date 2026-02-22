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
        return new AssembledSong(
            $song->songId->value,
            $song->title->value,
            $song->description->value,
            $song->songType->getName(),
            $song->songType->value,
            $song->orderNo->value,
            $song->arrangers->toGeneric()->map($this->toAssembledCreator(...))->toArray(),
            $song->composers->toGeneric()->map($this->toAssembledCreator(...))->toArray(),
            $song->lyricists->toGeneric()->map($this->toAssembledCreator(...))->toArray(),
        );
    }

    private function toAssembledCreator(Arranger|Composer|Lyricist $creator): AssembledCreator
    {
        $found = $this->creatorRepository->find($creator->creatorId);
        // Song Entity が成立している時点で $found が null になることはない
        assert(! is_null($found));

        return new AssembledCreator(
            $creator->creatorId->value,
            $found->name->value,
            $creator->orderNo->value,
        );
    }
}
