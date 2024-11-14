<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Port\UseCases\Get;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use App\Features\JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;

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
