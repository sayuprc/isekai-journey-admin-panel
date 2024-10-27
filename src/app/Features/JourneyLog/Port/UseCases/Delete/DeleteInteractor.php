<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Port\UseCases\Delete;

use App\Features\JourneyLog\Domain\Entities\JourneyLogId;
use App\Features\JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;

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
