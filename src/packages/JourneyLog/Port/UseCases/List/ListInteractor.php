<?php

declare(strict_types=1);

namespace JourneyLog\Port\UseCases\List;

use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;

class ListInteractor
{
    public function __construct(private readonly JourneyLogRepositoryInterface $client)
    {
    }

    public function handle(): ListResponse
    {
        return new ListResponse($this->client->listJourneyLogs());
    }
}
