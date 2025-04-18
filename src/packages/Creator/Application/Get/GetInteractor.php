<?php

declare(strict_types=1);

namespace Creator\Application\Get;

use Creator\Domain\Models\CreatorId;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\UseCases\Get\GetRequest;
use Creator\UseCases\Get\GetResponse;
use Creator\UseCases\Get\GetUseCaseInterface;

class GetInteractor implements GetUseCaseInterface
{
    public function __construct(private readonly CreatorRepositoryInterface $repository)
    {
    }

    public function handle(GetRequest $request): GetResponse
    {
        return new GetResponse($this->repository->find(new CreatorId($request->creatorId)));
    }
}
