<?php

declare(strict_types=1);

namespace JourneyLog\Application\Delete;

use JourneyLog\Domain\Entities\JourneyLogId;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\UseCases\Delete\DeleteRequest;
use JourneyLog\UseCases\Delete\DeleteUseCaseInterface;

class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(private readonly JourneyLogRepositoryInterface $client)
    {
    }

    public function handle(DeleteRequest $request): void
    {
        $this->client->deleteJourneyLog(new JourneyLogId($request->journeyLogId));
    }
}
