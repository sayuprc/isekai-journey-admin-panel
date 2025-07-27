<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\List;

use Creator\Domain\Models\Creator;

readonly class ListOutputData
{
    /**
     * @param array<Creator> $creators
     */
    public function __construct(public array $creators)
    {
    }
}
