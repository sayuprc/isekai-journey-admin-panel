<?php

declare(strict_types=1);

namespace Creator\UseCases\List;

interface ListUseCaseInterface
{
    public function handle(): ListResponse;
}
