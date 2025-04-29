<?php

declare(strict_types=1);

namespace Song\Application\List;

use Song\Domain\Repositories\SongRepositoryInterface;
use Song\UseCases\List\ListOutputData;
use Song\UseCases\List\ListUseCaseInterface;

class ListInteractor implements ListUseCaseInterface
{
    public function __construct(private readonly SongRepositoryInterface $repository)
    {
    }

    public function handle(): ListOutputData
    {
        return new ListOutputData($this->repository->all());
    }
}
