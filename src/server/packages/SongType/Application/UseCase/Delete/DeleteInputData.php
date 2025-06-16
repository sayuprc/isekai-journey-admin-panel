<?php

declare(strict_types=1);

namespace SongType\Application\UseCase\Delete;

class DeleteInputData
{
    public function __construct(public readonly string $songTypeId)
    {
    }
}
