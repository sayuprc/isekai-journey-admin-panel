<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Get;

readonly class GetInputData
{
    public function __construct(public string $creatorId)
    {
    }
}
