<?php

declare(strict_types=1);

namespace Creator\Application\Interactors;

use Creator\Application\UseCase\List\ListOutputData;
use Creator\Application\UseCase\List\ListUseCaseInterface;
use Creator\Domain\Models\CreatorRepositoryInterface;
use ResultType\Ok;
use ResultType\Result;

readonly class ListInteractor implements ListUseCaseInterface
{
    public function __construct(private CreatorRepositoryInterface $repository)
    {
    }

    public function handle(): Result
    {
        return new Ok(new ListOutputData($this->repository->all()));
    }
}
