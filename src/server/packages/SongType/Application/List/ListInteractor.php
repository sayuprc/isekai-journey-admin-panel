<?php

declare(strict_types=1);

namespace SongType\Application\List;

use SongType\Domain\Models\SongTypeRepositoryInterface;
use SongType\UseCases\List\ListOutputData;
use SongType\UseCases\List\ListUseCaseInterface;

class ListInteractor implements ListUseCaseInterface
{
    public function __construct(private readonly SongTypeRepositoryInterface $repository)
    {
    }

    public function handle(): ListOutputData
    {
        return new ListOutputData($this->repository->all());
    }
}
