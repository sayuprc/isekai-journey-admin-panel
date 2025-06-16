<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\List;

interface ListUseCaseInterface
{
    public function handle(): ListOutputData;
}
