<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\Create;

use Performer\Domain\Models\Performer;

readonly class CreateOutputData
{
    public function __construct(public Performer $performer)
    {
    }
}
