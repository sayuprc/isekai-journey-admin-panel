<?php

declare(strict_types=1);

namespace Creator\UseCases\Create;

class CreateRequest
{
    public function __construct(public readonly string $creatorName)
    {
    }
}
