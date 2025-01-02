<?php

declare(strict_types=1);

namespace JourneyLogLinkType\UseCases\Delete;

use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;

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
