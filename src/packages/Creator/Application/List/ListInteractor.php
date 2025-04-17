<?php

declare(strict_types=1);

namespace Creator\Application\List;

use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\UseCases\List\ListResponse;
use Creator\UseCases\List\ListUseCaseInterface;

class ListInteractor implements ListUseCaseInterface
{
    public function __construct(private readonly CreatorRepositoryInterface $repository)
    {
    }

    public function handle(): ListResponse
    {
        return new ListResponse($this->repository->listCreators());
    }
}
