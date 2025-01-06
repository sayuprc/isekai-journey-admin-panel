<?php

declare(strict_types=1);

namespace JourneyLog\Application\Get;

use JourneyLog\Domain\Entities\JourneyLogId;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\UseCases\Get\GetRequest;
use JourneyLog\UseCases\Get\GetResponse;
use JourneyLog\UseCases\Get\GetUseCaseInterface;

class GetInteractor implements GetUseCaseInterface
{
    public function __construct(private readonly JourneyLogRepositoryInterface $client)
    {
    }

    public function handle(GetRequest $request): GetResponse
    {
        return new GetResponse($this->client->getJourneyLog(new JourneyLogId($request->journeyLogId)));
    }
}
