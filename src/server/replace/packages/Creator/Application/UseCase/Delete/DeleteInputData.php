<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Delete;

readonly class DeleteInputData
{
    public function __construct(public string $creatorId)
    {
    }
}
