<?php

declare(strict_types=1);

namespace JourneyLog\Infrastructures;

use DateType\ImmutableDate;
use JourneyLog\Domain\Dtos\CreateJourneyLogLinkData;
use JourneyLog\Domain\Dtos\ReconstituteJourneyLogLinkData;
use JourneyLog\Domain\Models\FromOn;
use JourneyLog\Domain\Models\JourneyLog;
use JourneyLog\Domain\Models\JourneyLogFactoryInterface;
use JourneyLog\Domain\Models\JourneyLogId;
use JourneyLog\Domain\Models\JourneyLogLink\JourneyLogLink;
use JourneyLog\Domain\Models\JourneyLogLink\JourneyLogLinkId;
use JourneyLog\Domain\Models\JourneyLogLink\JourneyLogLinkName;
use JourneyLog\Domain\Models\Period;
use JourneyLog\Domain\Models\Story;
use JourneyLog\Domain\Models\ToOn;
use JourneyLog\Domain\Models\Url;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use Support\Contracts\UuidGeneratorInterface;
use Support\Domain\ValueObjects\OrderNo;

class JourneyLogFactory implements JourneyLogFactoryInterface
{
    public function __construct(private readonly UuidGeneratorInterface $uuid)
    {
    }

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
    ): JourneyLog {
        return new JourneyLog(
            new JourneyLogId($this->uuid->generate()),
            new Story($story),
            new Period(new FromOn($fromOn), new ToOn($toOn)),
            new OrderNo($orderNo),
            $this->createJourneyLogLinks($journeyLogLinks),
        );
    }

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
    ): JourneyLog {
        return new JourneyLog(
            new JourneyLogId($journeyLogId),
            new Story($story),
            new Period(new FromOn($fromOn), new ToOn($toOn)),
            new OrderNo($orderNo),
            $this->createJourneyLogLinks($journeyLogLinks),
        );
    }

    /**
     * @param array<CreateJourneyLogLinkData> $journeyLogLinks
     *
     * @return array<JourneyLogLink>
     */
    private function createJourneyLogLinks(array $journeyLogLinks): array
    {
        return array_map(
            fn (CreateJourneyLogLinkData $journeyLogLink): JourneyLogLink => new JourneyLogLink(
                new JourneyLogLinkId($this->uuid->generate()),
                new JourneyLogLinkName($journeyLogLink->journeyLogLinkName),
                new Url($journeyLogLink->url),
                new OrderNo($journeyLogLink->orderNo),
                new JourneyLogLinkTypeId($journeyLogLink->journeyLogLinkTypeId),
            ),
            $journeyLogLinks,
        );
    }

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
    ): JourneyLog {
        return new JourneyLog(
            new JourneyLogId($journeyLogId),
            new Story($story),
            new Period(new FromOn($fromOn), new ToOn($toOn)),
            new OrderNo($orderNo),
            $this->reconstituteJourneyLogLink($journeyLogLinks),
        );
    }

    /**
     * @param array<ReconstituteJourneyLogLinkData> $journeyLogLinks
     *
     * @return array<JourneyLogLink>
     */
    private function reconstituteJourneyLogLink(array $journeyLogLinks): array
    {
        return array_map(
            fn (ReconstituteJourneyLogLinkData $journeyLogLink): JourneyLogLink => new JourneyLogLink(
                new JourneyLogLinkId($journeyLogLink->journeyLogLinkId),
                new JourneyLogLinkName($journeyLogLink->journeyLogLinkName),
                new Url($journeyLogLink->url),
                new OrderNo($journeyLogLink->orderNo),
                new JourneyLogLinkTypeId($journeyLogLink->journeyLogLinkTypeId),
            ),
            $journeyLogLinks,
        );
    }
}
