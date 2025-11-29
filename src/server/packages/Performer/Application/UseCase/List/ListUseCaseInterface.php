<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\List;

interface ListUseCaseInterface
{
    public function handle(): ListOutputData;
}
