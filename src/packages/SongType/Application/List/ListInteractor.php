<?php

declare(strict_types=1);

namespace SongType\Application\List;

use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\UseCases\List\ListResponse;
use SongType\UseCases\List\ListUseCaseInterface;

class ListInteractor implements ListUseCaseInterface
{
    public function __construct(private readonly SongTypeRepositoryInterface $repository)
    {
    }

    public function handle(): ListResponse
    {
        return new ListResponse($this->repository->all());
    }
}
