<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\Edit;

use DateTimeImmutable;
use JourneyLog\Domain\Entities\FromOn;
use JourneyLog\Domain\Entities\JourneyLog;
use JourneyLog\Domain\Entities\JourneyLogId;
use JourneyLog\Domain\Entities\JourneyLogLink;
use JourneyLog\Domain\Entities\JourneyLogLinkId;
use JourneyLog\Domain\Entities\JourneyLogLinkName;
use JourneyLog\Domain\Entities\OrderNo;
use JourneyLog\Domain\Entities\Period;
use JourneyLog\Domain\Entities\Story;
use JourneyLog\Domain\Entities\ToOn;
use JourneyLog\Domain\Entities\Url;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use Shared\Uuid\UuidGeneratorInterface;

class EditInteractor
{
    public function __construct(
        private readonly JourneyLogRepositoryInterface $client,
        private readonly UuidGeneratorInterface $generator,
    ) {
    }

    public function handle(EditRequest $request): void
    {
        $journeyLog = new JourneyLog(
            new JourneyLogId($request->journeyLodId),
            new Story($request->story),
            new Period(
                new FromOn(new DateTimeImmutable($request->fromOn)),
                new ToOn(new DateTimeImmutable($request->toOn))
            ),
            new OrderNo($request->orderNo),
            $this->toJourneyLogLinks($request->journeyLogLinks),
        );

        $this->client->editJourneyLog($journeyLog);
    }

    /**
     * @param array<array{journey_log_link_name: string, url: string, order_no: int, journey_log_link_type_id: string}> $data
     *
     * @return JourneyLogLink[]
     */
    private function toJourneyLogLinks(array $data): array
    {
        $journeyLogLinks = [];

        foreach ($data as $link) {
            $journeyLogLinks[] = new JourneyLogLink(
                new JourneyLogLinkId($this->generator->generate()), // リンクはデリートインサートなのでダミー値でよい
                new JourneyLogLinkName($link['journey_log_link_name']),
                new Url($link['url']),
                new OrderNo($link['order_no']),
                new JourneyLogLinkTypeId($link['journey_log_link_type_id']),
            );
        }

        return $journeyLogLinks;
    }
}
