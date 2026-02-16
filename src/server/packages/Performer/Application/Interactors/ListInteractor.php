<?php

declare(strict_types=1);

namespace Performer\Application\Interactors;

use Performer\Application\UseCase\List\ListOutputData;
use Performer\Application\UseCase\List\ListUseCaseInterface;
use Performer\Domain\Models\PerformerRepositoryInterface;
use ResultType\Ok;
use ResultType\Result;

readonly class ListInteractor implements ListUseCaseInterface
{
    public function __construct(private PerformerRepositoryInterface $repository)
    {
    }

    public function handle(): Result
    {
        return new Ok(new ListOutputData($this->repository->all()));
    }
}
