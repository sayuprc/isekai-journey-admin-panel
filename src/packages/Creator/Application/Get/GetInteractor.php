<?php

declare(strict_types=1);

namespace Creator\Application\Get;

use Creator\Domain\Models\CreatorId;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\UseCases\Get\GetRequest;
use Creator\UseCases\Get\GetResponse;
use Creator\UseCases\Get\GetUseCaseInterface;
use Support\ResultType\Err;
use Support\ResultType\Ok;
use Support\ResultType\Result;

class GetInteractor implements GetUseCaseInterface
{
    public function __construct(private readonly CreatorRepositoryInterface $repository)
    {
    }

    /**
     * @return Result<GetResponse, string>
     */
    public function handle(GetRequest $request): Result
    {
        if (is_null($found = $this->repository->find(new CreatorId($request->creatorId)))) {
            return new Err("Creator not found: {$request->creatorId}");
        }

        return new Ok(new GetResponse($found));
    }
}
