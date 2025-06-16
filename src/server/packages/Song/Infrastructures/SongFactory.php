<?php

declare(strict_types=1);

namespace Song\Infrastructures;

use Creator\Domain\Models\CreatorId;
use Song\Domain\Dtos\CreateCreatorData;
use Song\Domain\Models\Creators\Arranger;
use Song\Domain\Models\Creators\Composer;
use Song\Domain\Models\Creators\Lyricist;
use Song\Domain\Models\Description;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Models\SongId;
use Song\Domain\Models\Title;
use SongType\Domain\Models\SongTypeId;
use Support\Contracts\UuidGeneratorInterface;
use Support\Domain\ValueObjects\OrderNo;

class SongFactory implements SongFactoryInterface
{
    public function __construct(private readonly UuidGeneratorInterface $uuid)
    {
    }

    /**
     * @param positive-int             $orderNo
     * @param array<CreateCreatorData> $lyricists
     * @param array<CreateCreatorData> $composers
     * @param array<CreateCreatorData> $arrangers
     */
    public function create(
        string $title,
        string $description,
        string $songTypeId,
        int $orderNo,
        array $lyricists,
        array $composers,
        array $arrangers,
    ): Song {
        return new Song(
            new SongId($this->uuid->generate()),
            new Title($title),
            new Description($description),
            new SongTypeId($songTypeId),
            new OrderNo($orderNo),
            array_map(
                fn (CreateCreatorData $creator) => new Lyricist(new CreatorId($creator->creatorId), new OrderNo($creator->orderNo)),
                $lyricists
            ),
            array_map(
                fn (CreateCreatorData $creator) => new Composer(new CreatorId($creator->creatorId), new OrderNo($creator->orderNo)),
                $composers
            ),
            array_map(
                fn (CreateCreatorData $creator) => new Arranger(new CreatorId($creator->creatorId), new OrderNo($creator->orderNo)),
                $arrangers
            ),
        );
    }
}
