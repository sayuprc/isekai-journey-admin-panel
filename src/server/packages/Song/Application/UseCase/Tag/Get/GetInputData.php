<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Tag\Get;

readonly class GetInputData
{
    public function __construct(public string $songTagId)
    {
    }
}
