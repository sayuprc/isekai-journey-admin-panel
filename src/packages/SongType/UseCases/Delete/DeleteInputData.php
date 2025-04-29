<?php

declare(strict_types=1);

namespace SongType\UseCases\Delete;

class DeleteInputData
{
    public function __construct(public readonly string $songTypeId)
    {
    }
}
