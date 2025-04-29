<?php

declare(strict_types=1);

namespace Creator\UseCases\Create;

class CreateInputData
{
    public function __construct(public readonly string $creatorName)
    {
    }
}
