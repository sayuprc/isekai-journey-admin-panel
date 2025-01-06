<?php

declare(strict_types=1);

namespace JourneyLogLinkType\UseCases\List;

interface ListUseCaseInterface
{
    public function handle(): ListResponse;
}
