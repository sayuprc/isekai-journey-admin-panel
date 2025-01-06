<?php

declare(strict_types=1);

namespace JourneyLog\Port\UseCases\Delete;

use JourneyLog\Domain\Entities\JourneyLogId;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;

class DeleteInteractor
{
    public function __construct(private readonly JourneyLogRepositoryInterface $client)
    {
    }

    public function handle(DeleteRequest $request): void
    {
        $this->client->deleteJourneyLog(new JourneyLogId($request->journeyLogId));
    }
}
