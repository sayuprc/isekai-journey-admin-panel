<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Port\UseCases\Get;

use App\Features\JourneyLog\Domain\Entities\JourneyLogId;
use App\Features\JourneyLog\Domain\Repositories\JourneyLogServiceClientInterface;

class GetInteractor
{
    public function __construct(private readonly JourneyLogServiceClientInterface $client)
    {
    }

    public function handle(GetRequest $request): GetResponse
    {
        return new GetResponse($this->client->getJourneyLog(new JourneyLogId($request->journeyLogId)));
    }
}
