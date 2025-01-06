<?php

declare(strict_types=1);

namespace JourneyLog\Application\List;

use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\UseCases\List\ListResponse;
use JourneyLog\UseCases\List\ListUseCaseInterface;

class ListInteractor implements ListUseCaseInterface
{
    public function __construct(private readonly JourneyLogRepositoryInterface $client)
    {
    }

    public function handle(): ListResponse
    {
        return new ListResponse($this->client->listJourneyLogs());
    }
}
