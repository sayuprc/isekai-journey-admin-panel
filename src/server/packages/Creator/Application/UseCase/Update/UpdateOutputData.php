<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Update;

use Creator\Domain\Models\Creator;

class UpdateOutputData
{
    public function __construct(public readonly Creator $creator)
    {
    }
}
