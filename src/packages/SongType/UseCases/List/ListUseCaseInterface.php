<?php

declare(strict_types=1);

namespace SongType\UseCases\List;

interface ListUseCaseInterface
{
    public function handle(): ListOutputData;
}
