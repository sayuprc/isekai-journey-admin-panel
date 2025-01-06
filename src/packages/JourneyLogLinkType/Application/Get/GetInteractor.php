<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Get;

use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Get\GetRequest;
use JourneyLogLinkType\UseCases\Get\GetResponse;
use JourneyLogLinkType\UseCases\Get\GetUseCaseInterface;

class GetInteractor implements GetUseCaseInterface
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
