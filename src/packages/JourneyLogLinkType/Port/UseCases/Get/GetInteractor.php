<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Port\UseCases\Get;

use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;

class GetInteractor
{
    public function __construct(private readonly JourneyLogLinkTypeRepositoryInterface $client)
    {
    }

    public function handle(GetRequest $request): GetResponse
    {
        $journeyLogLinkTypeId = new JourneyLogLinkTypeId($request->journeyLogLinkTypeId);

        return new GetResponse($this->client->getJourneyLogLinkType($journeyLogLinkTypeId));
    }
}
