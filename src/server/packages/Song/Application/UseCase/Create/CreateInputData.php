<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Create;

use DateType\ImmutableDate;
use Song\Domain\Dtos\CreateCreatorData;

class CreateInputData
{
    /**
     * @param array<CreateCreatorData> $lyricists
     * @param array<CreateCreatorData> $composers
     * @param array<CreateCreatorData> $arrangers
     */
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly ImmutableDate $releasedOn,
        public readonly string $songTypeId,
        public readonly array $lyricists,
        public readonly array $composers,
        public readonly array $arrangers,
    ) {
    }
}
