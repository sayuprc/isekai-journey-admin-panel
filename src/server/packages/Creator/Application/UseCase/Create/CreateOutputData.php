<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Create;

use Creator\Domain\Models\Creator;

class CreateOutputData
{
    public function __construct(public readonly Creator $creator)
    {
    }
}
