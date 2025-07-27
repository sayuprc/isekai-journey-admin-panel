<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Create;

use DateType\ImmutableDate;
use Song\Domain\Dtos\CreateCreatorData;

readonly class CreateInputData
{
    /**
     * @param positive-int             $orderNo
     * @param array<CreateCreatorData> $lyricists
     * @param array<CreateCreatorData> $composers
     * @param array<CreateCreatorData> $arrangers
     */
    public function __construct(
        public string $title,
        public string $description,
        public ImmutableDate $releasedOn,
        public string $songTypeId,
        public int $orderNo,
        public array $lyricists,
        public array $composers,
        public array $arrangers,
    ) {
    }
}
