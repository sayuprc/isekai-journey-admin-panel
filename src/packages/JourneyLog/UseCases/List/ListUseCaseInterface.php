<?php

declare(strict_types=1);

namespace JourneyLog\UseCases\List;

interface ListUseCaseInterface
{
    public function handle(): ListResponse;
}
