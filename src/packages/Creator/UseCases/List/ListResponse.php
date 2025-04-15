<?php

declare(strict_types=1);

namespace Creator\UseCases\List;

use Creator\Domain\Models\Creator;

class ListResponse
{
    /**
     * @param array<Creator> $creators
     */
    public function __construct(public readonly array $creators)
    {
    }
}
