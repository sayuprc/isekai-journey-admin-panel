<?php

declare(strict_types=1);

namespace JourneyLog\Domain\Models;

use DateTimeInterface;
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
        DateTimeInterface $fromOn,
        DateTimeInterface $toOn,
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
        DateTimeInterface $fromOn,
        DateTimeInterface $toOn,
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
        DateTimeInterface $fromOn,
        DateTimeInterface $toOn,
        int $orderNo,
        array $journeyLogLinks
    ): JourneyLog;
}
