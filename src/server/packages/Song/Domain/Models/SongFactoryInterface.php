<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use DateType\ImmutableDate;
use Song\Domain\Dtos\CreateCreatorData;

interface SongFactoryInterface
{
    /**
     * @param positive-int             $orderNo
     * @param array<CreateCreatorData> $lyricists
     * @param array<CreateCreatorData> $composers
     * @param array<CreateCreatorData> $arrangers
     */
    public function create(
        string $title,
        string $description,
        ImmutableDate $releasedOn,
        string $songTypeId,
        int $orderNo,
        array $lyricists,
        array $composers,
        array $arrangers,
    ): Song;
}
