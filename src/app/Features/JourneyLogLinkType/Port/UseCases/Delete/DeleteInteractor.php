<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Port\UseCases\Delete;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use App\Features\JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;

class DeleteInteractor
{
    public function __construct(private readonly JourneyLogLinkTypeRepositoryInterface $client)
    {
    }

    public function handle(DeleteRequest $request): void
    {
        $journeyLogLinkTypeId = new JourneyLogLinkTypeId($request->journeyLogLinkTypeId);

        $this->client->deleteJourneyLogLinkType($journeyLogLinkTypeId);
    }
}
