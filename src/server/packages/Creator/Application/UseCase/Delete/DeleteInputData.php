<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Delete;

class DeleteInputData
{
    public function __construct(public readonly string $creatorId)
    {
    }
}
