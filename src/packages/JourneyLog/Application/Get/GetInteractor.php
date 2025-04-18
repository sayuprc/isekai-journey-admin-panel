<?php

declare(strict_types=1);

namespace JourneyLog\Application\Get;

use JourneyLog\Domain\Models\JourneyLogId;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\UseCases\Get\GetRequest;
use JourneyLog\UseCases\Get\GetResponse;
use JourneyLog\UseCases\Get\GetUseCaseInterface;

class GetInteractor implements GetUseCaseInterface
{
    public function __construct(private readonly JourneyLogRepositoryInterface $repository)
    {
    }

    public function handle(GetRequest $request): GetResponse
    {
        return new GetResponse($this->repository->find(new JourneyLogId($request->journeyLogId)));
    }
}
