<?php

declare(strict_types=1);

namespace Creator\UseCases\Get;

use Creator\Domain\Models\Creator;

class GetResponse
{
    public function __construct(public readonly Creator $creator)
    {
    }
}
