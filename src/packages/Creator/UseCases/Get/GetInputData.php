<?php

declare(strict_types=1);

namespace Creator\UseCases\Get;

class GetInputData
{
    public function __construct(public readonly string $creatorId)
    {
    }
}
