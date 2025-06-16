<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Create;

use Song\Domain\Dtos\CreateCreatorData;
use Song\Domain\Dtos\CreateNonLinkArchiveData;
use Song\Domain\Dtos\CreateTwitterArchiveData;
use Song\Domain\Dtos\CreateYouTubeArchiveData;

class CreateInputData
{
    /**
     * @param positive-int                                                                      $orderNo
     * @param array<CreateCreatorData>                                                          $lyricists
     * @param array<CreateCreatorData>                                                          $composers
     * @param array<CreateCreatorData>                                                          $arrangers
     * @param array<CreateYouTubeArchiveData|CreateTwitterArchiveData|CreateNonLinkArchiveData> $archives
     */
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $songTypeId,
        public readonly int $orderNo,
        public readonly array $lyricists,
        public readonly array $composers,
        public readonly array $arrangers,
        public readonly array $archives,
    ) {
    }
}
