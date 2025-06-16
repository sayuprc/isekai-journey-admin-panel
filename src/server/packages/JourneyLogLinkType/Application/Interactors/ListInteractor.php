<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\Interactors;

use JourneyLogLinkType\Application\UseCase\List\ListOutputData;
use JourneyLogLinkType\Application\UseCase\List\ListUseCaseInterface;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeRepositoryInterface;

class ListInteractor implements ListUseCaseInterface
{
    public function __construct(private readonly JourneyLogLinkTypeRepositoryInterface $repository)
    {
    }

    public function handle(): ListOutputData
    {
        return new ListOutputData($this->repository->all());
    }
}
