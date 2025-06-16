<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Create;

class CreateInputData
{
    public function __construct(public readonly string $creatorName)
    {
    }
}
