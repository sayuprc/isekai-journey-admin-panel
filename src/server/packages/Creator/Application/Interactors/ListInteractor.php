<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use Creator\Application\UseCase\List\ListOutputData;
use Creator\Application\UseCase\List\ListUseCaseInterface;
use Creator\Domain\Models\CreatorRepositoryInterface;

readonly class ListInteractor implements ListUseCaseInterface
{
    public function __construct(private CreatorRepositoryInterface $repository)
    {
    }

    public function handle(): ListOutputData
    {
        return new ListOutputData($this->repository->all());
    }
}
