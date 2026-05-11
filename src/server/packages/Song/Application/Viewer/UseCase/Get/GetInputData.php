<?php

declare(strict_types=1);

namespace Song\Application\Viewer\UseCase\Get;

readonly class GetInputData
{
    public function __construct(public string $songId)
    {
    }
}
