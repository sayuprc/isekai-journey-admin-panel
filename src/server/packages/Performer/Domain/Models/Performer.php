<?php

declare(strict_types=1);

namespace Performer\Domain\Models;

readonly class Performer
{
    public function __construct(
        public PerformerId $performerId,
        public PerformerName $performerName,
    ) {
    }
}
