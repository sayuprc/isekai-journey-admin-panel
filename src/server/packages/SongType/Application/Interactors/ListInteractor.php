<?php

declare(strict_types=1);

namespace SongType\Application\Interactors;

use SongType\Application\UseCase\List\ListOutputData;
use SongType\Application\UseCase\List\ListUseCaseInterface;
use SongType\Domain\Models\SongType;

readonly class ListInteractor implements ListUseCaseInterface
{
    public function handle(): ListOutputData
    {
        return new ListOutputData(SongType::cases());
    }
}
