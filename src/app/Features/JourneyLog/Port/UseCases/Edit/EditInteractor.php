<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Port\UseCases\Edit;

use App\Features\JourneyLog\Domain\Entities\Period;
use App\Features\JourneyLog\Domain\Entities\JourneyLog;
use App\Features\JourneyLog\Domain\Entities\JourneyLogId;
use App\Features\JourneyLog\Domain\Entities\OrderNo;
use App\Features\JourneyLog\Domain\Entities\Story;
use App\Features\JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use DateTimeImmutable;

class EditInteractor
{
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
        );

        $this->client->editJourneyLog($journeyLog);
    }
}
