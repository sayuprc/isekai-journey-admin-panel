<?php

declare(strict_types=1);

namespace Performer\Application\Interactors;

use Performer\Application\UseCase\List\ListOutputData;
use Performer\Application\UseCase\List\ListUseCaseInterface;
use Performer\Domain\Models\PerformerRepositoryInterface;

readonly class ListInteractor implements ListUseCaseInterface
{
    public function __construct(private PerformerRepositoryInterface $repository)
    {
    }

    public function handle(): ListOutputData
    {
        return new ListOutputData($this->repository->all());
    }
}
