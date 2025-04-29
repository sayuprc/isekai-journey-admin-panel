<?php

declare(strict_types=1);

namespace Creator\UseCases\Delete;

class DeleteInputData
{
    public function __construct(public readonly string $creatorId)
    {
    }
}
