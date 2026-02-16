<?php

declare(strict_types=1);

namespace SongType\Application\Interactors;

use ResultType\Ok;
use ResultType\Result;
use SongType\Application\UseCase\List\ListOutputData;
use SongType\Application\UseCase\List\ListUseCaseInterface;
use SongType\Domain\Models\SongType;

readonly class ListInteractor implements ListUseCaseInterface
{
    public function handle(): Result
    {
        return new Ok(new ListOutputData(SongType::cases()));
    }
}
