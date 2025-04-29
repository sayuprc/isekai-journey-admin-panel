<?php

declare(strict_types=1);

namespace SongType\UseCases\Delete;

class DeleteRequest
{
    public function __construct(public readonly string $songTypeId)
    {
    }
}
