<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Song\Domain\Dtos\CreateCreatorData;
use Song\Domain\Dtos\CreateNonLinkArchiveData;
use Song\Domain\Dtos\CreateTwitterArchiveData;
use Song\Domain\Dtos\CreateYouTubeArchiveData;

interface SongFactoryInterface
{
    /**
     * @param positive-int                                                                      $orderNo
     * @param array<CreateCreatorData>                                                          $lyricists
     * @param array<CreateCreatorData>                                                          $composers
     * @param array<CreateCreatorData>                                                          $arrangers
     * @param array<CreateNonLinkArchiveData|CreateTwitterArchiveData|CreateYouTubeArchiveData> $archives
     */
    public function create(
        string $title,
        string $description,
        string $songTypeId,
        int $orderNo,
        array $lyricists,
        array $composers,
        array $arrangers,
        array $archives,
    ): Song;
}
