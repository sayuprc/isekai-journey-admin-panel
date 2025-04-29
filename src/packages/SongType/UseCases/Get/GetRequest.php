<?php

declare(strict_types=1);

namespace SongType\UseCases\Get;

class GetRequest
{
    public function __construct(public readonly string $songTypeId)
    {
    }
}
