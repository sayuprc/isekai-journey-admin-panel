<?php

declare(strict_types=1);

namespace JourneyLog\Application\UseCase\Create;

use DateType\ImmutableDate;
use JourneyLog\Domain\Dtos\CreateJourneyLogLinkData;

class CreateInputData
{
    /**
     * @param positive-int                    $orderNo
     * @param array<CreateJourneyLogLinkData> $journeyLogLinks
     */
    public function __construct(
        public readonly string $story,
        public readonly ImmutableDate $fromOn,
        public readonly ImmutableDate $toOn,
        public readonly int $orderNo,
        public readonly array $journeyLogLinks,
    ) {
    }
}
