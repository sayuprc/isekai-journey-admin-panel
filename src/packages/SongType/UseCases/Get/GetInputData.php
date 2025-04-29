<?php

declare(strict_types=1);

namespace SongType\UseCases\Get;

class GetInputData
{
    public function __construct(public readonly string $songTypeId)
    {
    }
}
