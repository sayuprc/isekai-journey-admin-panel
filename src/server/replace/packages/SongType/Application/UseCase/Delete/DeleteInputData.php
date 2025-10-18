<?php

declare(strict_types=1);

namespace SongType\Application\UseCase\Delete;

readonly class DeleteInputData
{
    public function __construct(public string $songTypeId)
    {
    }
}
