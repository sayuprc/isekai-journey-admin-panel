<?php

declare(strict_types=1);

namespace JourneyLog\Application\UseCase\Edit;

use DateType\ImmutableDate;
use JourneyLog\Domain\Dtos\CreateJourneyLogLinkData;

readonly class EditInputData
{
    /**
     * @param positive-int                    $orderNo
     * @param array<CreateJourneyLogLinkData> $journeyLogLinks
     */
    public function __construct(
        public string $journeyLogId,
        public string $story,
        public ImmutableDate $fromOn,
        public ImmutableDate $toOn,
        public int $orderNo,
        public array $journeyLogLinks,
    ) {
    }
}
