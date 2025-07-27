<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Create;

readonly class CreateInputData
{
    public function __construct(public string $creatorName)
    {
    }
}
