<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Port\UseCases\Create;

use App\Features\JourneyLog\Domain\Entities\Period;
use App\Features\JourneyLog\Domain\Entities\JourneyLog;
use App\Features\JourneyLog\Domain\Entities\JourneyLogId;
use App\Features\JourneyLog\Domain\Entities\OrderNo;
use App\Features\JourneyLog\Domain\Entities\Story;
use App\Features\JourneyLog\Domain\Repositories\JourneyLogServiceClientInterface;
use DateTimeImmutable;

class CreateInteractor
{
    private const string DUMMY_UUID = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

    public function __construct(private readonly JourneyLogServiceClientInterface $client)
    {
    }

    public function handle(CreateRequest $request): void
    {
        $journeyLog = new JourneyLog(
            new JourneyLogId(self::DUMMY_UUID), // 新規登録に ID は不要なのでダミーの ID を持たせている
            new Story($request->story),
            new Period(new DateTimeImmutable($request->fromOn), new DateTimeImmutable($request->toOn)),
            new OrderNo($request->orderNo),
        );

        $this->client->createJourneyLog($journeyLog);
    }
}
