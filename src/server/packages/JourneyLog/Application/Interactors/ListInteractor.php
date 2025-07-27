<?php

declare(strict_types=1);

namespace JourneyLog\Application\Interactors;

use JourneyLog\Application\UseCase\List\ListOutputData;
use JourneyLog\Application\UseCase\List\ListUseCaseInterface;
use JourneyLog\Domain\Models\JourneyLogRepositoryInterface;

readonly class ListInteractor implements ListUseCaseInterface
{
    public function __construct(private JourneyLogRepositoryInterface $repository)
    {
    }

    public function handle(): ListOutputData
    {
        return new ListOutputData($this->repository->all());
    }
}
