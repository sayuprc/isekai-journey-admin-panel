<?php

declare(strict_types=1);

namespace JourneyLog\Port\UseCases\Get;

use JourneyLog\Domain\Entities\JourneyLogId;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;

class GetInteractor
{
    public function __construct(private readonly JourneyLogRepositoryInterface $client)
    {
    }

    public function handle(GetRequest $request): GetResponse
    {
        return new GetResponse($this->client->getJourneyLog(new JourneyLogId($request->journeyLogId)));
    }
}
