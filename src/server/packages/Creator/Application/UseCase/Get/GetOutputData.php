<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Get;

use Creator\Domain\Models\Creator;

class GetOutputData
{
    public function __construct(public readonly Creator $creator)
    {
    }
}
