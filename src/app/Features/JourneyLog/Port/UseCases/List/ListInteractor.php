<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Port\UseCases\List;

use App\Features\JourneyLog\Domain\Repositories\JourneyLogServiceClientInterface;

class ListInteractor
{
    public function __construct(private readonly JourneyLogServiceClientInterface $client)
    {
    }

    public function handle(): ListResponse
    {
        return new ListResponse($this->client->listJourneyLogs());
    }
}
