<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\List;

use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use JourneyLogLinkType\UseCases\List\ListOutputData;
use JourneyLogLinkType\UseCases\List\ListUseCaseInterface;

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
