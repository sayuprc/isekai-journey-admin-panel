<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Port\UseCases\Edit;

use App\Features\JourneyLog\Domain\Entities\JourneyLog;
use App\Features\JourneyLog\Domain\Entities\JourneyLogId;
use App\Features\JourneyLog\Domain\Entities\JourneyLogLink;
use App\Features\JourneyLog\Domain\Entities\JourneyLogLinkId;
use App\Features\JourneyLog\Domain\Entities\JourneyLogLinkName;
use App\Features\JourneyLog\Domain\Entities\OrderNo;
use App\Features\JourneyLog\Domain\Entities\Period;
use App\Features\JourneyLog\Domain\Entities\Story;
use App\Features\JourneyLog\Domain\Entities\Url;
use App\Features\JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use DateTimeImmutable;

class EditInteractor
{
    private const string DUMMY_UUID = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

    public function __construct(private readonly JourneyLogRepositoryInterface $client)
    {
    }

    public function handle(EditRequest $request): void
    {
        $journeyLog = new JourneyLog(
            new JourneyLogId($request->journeyLodId),
            new Story($request->story),
            new Period(new DateTimeImmutable($request->fromOn), new DateTimeImmutable($request->toOn)),
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
                new JourneyLogLinkId(self::DUMMY_UUID), // リンクはデリートインサートなのでダミー値でよい
                new JourneyLogLinkName($link['journey_log_link_name']),
                new Url($link['url']),
                new OrderNo($link['order_no']),
                new JourneyLogLinkTypeId($link['journey_log_link_type_id']),
            );
        }

        return $journeyLogLinks;
    }
}
