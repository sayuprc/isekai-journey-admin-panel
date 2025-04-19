<?php

declare(strict_types=1);

namespace JourneyLog\Application\Get;

use JourneyLog\Domain\Models\JourneyLogId;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\UseCases\Get\GetRequest;
use JourneyLog\UseCases\Get\GetResponse;
use JourneyLog\UseCases\Get\GetUseCaseInterface;
use Support\Result\Err;
use Support\Result\Ok;
use Support\Result\Result;

class GetInteractor implements GetUseCaseInterface
{
    public function __construct(private readonly JourneyLogRepositoryInterface $repository)
    {
    }

    /**
     * @return Result<GetResponse, string>
     */
    public function handle(GetRequest $request): Result
    {
        if (is_null($found = $this->repository->find(new JourneyLogId($request->journeyLogId)))) {
            return new Err("JourneyLog not found: {$request->journeyLogId}");
        }

        return new Ok(new GetResponse($found));
    }
}
