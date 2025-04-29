<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\Edit;

use DateTimeInterface;
use JourneyLog\Domain\Dtos\CreateJourneyLogLinkData;

class EditInputData
{
    /**
     * @param positive-int                    $orderNo
     * @param array<CreateJourneyLogLinkData> $journeyLogLinks
     */
    public function __construct(
        public readonly string $journeyLogId,
        public readonly string $story,
        public readonly DateTimeInterface $fromOn,
        public readonly DateTimeInterface $toOn,
        public readonly int $orderNo,
        public readonly array $journeyLogLinks,
    ) {
    }
}
