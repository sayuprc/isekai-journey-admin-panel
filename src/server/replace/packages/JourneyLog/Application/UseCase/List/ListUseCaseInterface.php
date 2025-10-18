<?php

declare(strict_types=1);

namespace JourneyLog\Application\UseCase\List;

interface ListUseCaseInterface
{
    public function handle(): ListOutputData;
}
