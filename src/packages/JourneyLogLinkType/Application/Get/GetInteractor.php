<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Get;

use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\Get\GetRequest;
use JourneyLogLinkType\UseCases\Get\GetResponse;
use JourneyLogLinkType\UseCases\Get\GetUseCaseInterface;
use Support\ResultType\Err;
use Support\ResultType\Ok;
use Support\ResultType\Result;

class GetInteractor implements GetUseCaseInterface
{
    public function __construct(private readonly JourneyLogLinkTypeRepositoryInterface $repository)
    {
    }

    /**
     * @return Result<GetResponse, string>
     */
    public function handle(GetRequest $request): Result
    {
        if (is_null($found = $this->repository->find(new JourneyLogLinkTypeId($request->journeyLogLinkTypeId)))) {
            return new Err("JourneyLogLinkType not found: {$request->journeyLogLinkTypeId}");
        }

        return new Ok(new GetResponse($found));
    }
}
