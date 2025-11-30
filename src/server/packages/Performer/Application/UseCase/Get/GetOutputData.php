<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\Get;

use Performer\Domain\Models\Performer;

readonly class GetOutputData
{
    public function __construct(public Performer $performer)
    {
    }
}
