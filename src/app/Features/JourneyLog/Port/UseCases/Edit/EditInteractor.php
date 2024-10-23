<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Port\UseCases\Edit;

use App\Features\JourneyLog\Domain\Entities\EventDate;
use App\Features\JourneyLog\Domain\Entities\JourneyLog;
use App\Features\JourneyLog\Domain\Entities\JourneyLogId;
use App\Features\JourneyLog\Domain\Entities\OrderNo;
use App\Features\JourneyLog\Domain\Entities\Story;
use App\Features\JourneyLog\Domain\Entities\Summary;
use App\Features\JourneyLog\Domain\Repositories\JourneyLogServiceClientInterface;
use DateTimeImmutable;

class EditInteractor
{
    public function __construct(private readonly JourneyLogServiceClientInterface $client)
    {
    }

    public function handle(EditRequest $request): void
    {
        $journeyLog = new JourneyLog(
            new JourneyLogId($request->journeyLodId),
            new Summary($request->summary),
            new Story($request->story),
            new EventDate(new DateTimeImmutable($request->fromOn), new DateTimeImmutable($request->toOn)),
            new OrderNo($request->orderNo),
        );

        $this->client->editJourneyLog($journeyLog);
    }
}
