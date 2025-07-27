<?php

declare(strict_types=1);

namespace JourneyLog\Application\UseCase\Create;

use DateType\ImmutableDate;
use JourneyLog\Domain\Dtos\CreateJourneyLogLinkData;

readonly class CreateInputData
{
    /**
     * @param positive-int                    $orderNo
     * @param array<CreateJourneyLogLinkData> $journeyLogLinks
     */
    public function __construct(
        public string $story,
        public ImmutableDate $fromOn,
        public ImmutableDate $toOn,
        public int $orderNo,
        public array $journeyLogLinks,
    ) {
    }
}
