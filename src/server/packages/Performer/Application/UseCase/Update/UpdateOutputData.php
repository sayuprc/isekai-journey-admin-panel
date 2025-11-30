<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\Update;

use Performer\Domain\Models\Performer;

readonly class UpdateOutputData
{
    public function __construct(public Performer $performer)
    {
    }
}
