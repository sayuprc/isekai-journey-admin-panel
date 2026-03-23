<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Tag\Create;

readonly class CreateInputData
{
    public function __construct(public string $name)
    {
    }
}
