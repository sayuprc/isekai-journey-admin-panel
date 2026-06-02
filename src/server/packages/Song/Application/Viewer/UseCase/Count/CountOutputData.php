<?php

declare(strict_types=1);

namespace Song\Application\Viewer\UseCase\Count;

readonly class CountOutputData
{
    public function __construct(public int $songCount)
    {
    }
}
