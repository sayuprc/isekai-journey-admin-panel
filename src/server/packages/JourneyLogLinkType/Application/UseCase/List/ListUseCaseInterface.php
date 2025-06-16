<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\UseCase\List;

interface ListUseCaseInterface
{
    public function handle(): ListOutputData;
}
