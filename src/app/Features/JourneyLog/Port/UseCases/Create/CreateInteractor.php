<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Port\UseCases\Create;

use App\Features\JourneyLog\Domain\Entities\JourneyLog;
use App\Features\JourneyLog\Domain\Entities\JourneyLogId;
use App\Features\JourneyLog\Domain\Entities\Link;
use App\Features\JourneyLog\Domain\Entities\LinkId;
use App\Features\JourneyLog\Domain\Entities\LinkName;
use App\Features\JourneyLog\Domain\Entities\LinkTypeId;
use App\Features\JourneyLog\Domain\Entities\OrderNo;
use App\Features\JourneyLog\Domain\Entities\Period;
use App\Features\JourneyLog\Domain\Entities\Story;
use App\Features\JourneyLog\Domain\Entities\Url;
use App\Features\JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use DateTimeImmutable;

class CreateInteractor
{
    private const string DUMMY_UUID = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

    public function __construct(private readonly JourneyLogRepositoryInterface $client)
    {
    }

    public function handle(CreateRequest $request): void
    {
        $journeyLog = new JourneyLog(
            new JourneyLogId(self::DUMMY_UUID), // 新規登録に ID は不要なのでダミーの ID を持たせている
            new Story($request->story),
            new Period(new DateTimeImmutable($request->fromOn), new DateTimeImmutable($request->toOn)),
            new OrderNo($request->orderNo),
            $this->toLinks($request->links),
        );

        $this->client->createJourneyLog($journeyLog);
    }

    /**
     * @param array<array{link_name: string, url: string, order_no: int, link_type_id: string}> $data
     *
     * @return Link[]
     */
    private function toLinks(array $data): array
    {
        $links = [];

        foreach ($data as $link) {
            $links[] = new Link(
                new LinkId(self::DUMMY_UUID), // リンクはデリートインサートなのでダミー値でよい
                new LinkName($link['link_name']),
                new Url($link['url']),
                new OrderNo($link['order_no']),
                new LinkTypeId($link['link_type_id']),
            );
        }

        return $links;
    }
}
