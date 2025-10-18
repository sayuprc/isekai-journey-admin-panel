<?php

declare(strict_types=1);

namespace JourneyLog\Domain\Models;

use DateType\ImmutableDate;
use JourneyLog\Domain\Dtos\CreateJourneyLogLinkData;
use JourneyLog\Domain\Dtos\ReconstituteJourneyLogLinkData;

interface JourneyLogFactoryInterface
{
    /**
     * @param positive-int                    $orderNo
     * @param array<CreateJourneyLogLinkData> $journeyLogLinks
     */
    public function create(
        string $story,
        ImmutableDate $fromOn,
        ImmutableDate $toOn,
        int $orderNo,
        array $journeyLogLinks
    ): JourneyLog;

    /**
     * @param positive-int                    $orderNo
     * @param array<CreateJourneyLogLinkData> $journeyLogLinks
     */
    public function createForUpdate(
        string $journeyLogId,
        string $story,
        ImmutableDate $fromOn,
        ImmutableDate $toOn,
        int $orderNo,
        array $journeyLogLinks
    ): JourneyLog;

    /**
     * @param positive-int                          $orderNo
     * @param array<ReconstituteJourneyLogLinkData> $journeyLogLinks
     */
    public function reconstitute(
        string $journeyLogId,
        string $story,
        ImmutableDate $fromOn,
        ImmutableDate $toOn,
        int $orderNo,
        array $journeyLogLinks
    ): JourneyLog;
}
