<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Port\UseCases\List;

use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;

class ListInteractor
{
    public function __construct(private readonly JourneyLogLinkTypeRepositoryInterface $client)
    {
    }

    public function handle(): ListResponse
    {
        return new ListResponse($this->client->listJourneyLogLinkTypes());
    }
}
