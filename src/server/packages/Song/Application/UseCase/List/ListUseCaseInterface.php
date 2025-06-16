<?php

declare(strict_types=1);

namespace Song\Application\UseCase\List;

interface ListUseCaseInterface
{
    public function handle(): ListOutputData;
}
