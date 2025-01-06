<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Delete;

use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Delete\DeleteRequest;
use JourneyLogLinkType\UseCases\Delete\DeleteUseCaseInterface;

class DeleteInteractor implements DeleteUseCaseInterface
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
