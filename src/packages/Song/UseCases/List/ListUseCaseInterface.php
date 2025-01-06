<?php

declare(strict_types=1);

namespace Song\UseCases\List;

interface ListUseCaseInterface
{
    public function handle(): ListResponse;
}
